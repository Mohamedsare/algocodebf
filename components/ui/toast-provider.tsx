'use client'

import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
} from 'react'
import type { FlashType } from '@/lib/flash'

type ToastItem = { id: string; type: FlashType; message: string }

const TOAST_MS = 2300

function iconFor(type: FlashType): string {
  switch (type) {
    case 'success':
      return 'fa-check-circle'
    case 'error':
      return 'fa-exclamation-circle'
    case 'warning':
      return 'fa-exclamation-triangle'
    default:
      return 'fa-info-circle'
  }
}

type ToastApi = {
  push: (type: FlashType, message: string) => void
}

const ToastContext = createContext<ToastApi | null>(null)

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<ToastItem[]>([])

  const push = useCallback((type: FlashType, message: string) => {
    const text = message.trim()
    if (!text) return
    const id = `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`
    setItems(prev => [...prev, { id, type, message: text }])
    window.setTimeout(() => {
      setItems(prev => prev.filter(x => x.id !== id))
    }, TOAST_MS)
  }, [])

  const value = useMemo(() => ({ push }), [push])

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div
        className="bf-toast-stack"
        aria-live="polite"
        aria-relevant="additions text"
      >
        {items.map(t => (
          <div
            key={t.id}
            className={`bf-toast bf-toast--${t.type}`}
            role="status"
          >
            <i
              className={`fas ${iconFor(t.type)} bf-toast__ico`}
              aria-hidden
            />
            <span className="bf-toast__msg">{t.message}</span>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  )
}

export function useToast(): ToastApi & {
  success: (m: string) => void
  error: (m: string) => void
  info: (m: string) => void
  warning: (m: string) => void
} {
  const ctx = useContext(ToastContext)
  if (!ctx) {
    throw new Error('useToast doit être utilisé sous <ToastProvider>.')
  }
  return {
    ...ctx,
    success: (m: string) => ctx.push('success', m),
    error: (m: string) => ctx.push('error', m),
    info: (m: string) => ctx.push('info', m),
    warning: (m: string) => ctx.push('warning', m),
  }
}
