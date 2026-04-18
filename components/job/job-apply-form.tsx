'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { Send, CheckCircle, FileText, AlertCircle } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { createClient } from '@/lib/supabase/client'

interface JobApplyFormProps {
  jobId: number
}

/**
 * Formulaire de candidature à une offre.
 * - Le CV est repris du profil utilisateur (`profiles.cv_path`), comme en PHP.
 * - Si l'utilisateur n'a pas de CV sur son profil, on l'invite à en uploader un.
 * - Seul le champ `cover_letter` est saisi ici.
 */
export function JobApplyForm({ jobId }: JobApplyFormProps) {
  const router = useRouter()
  const [coverLetter, setCoverLetter] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)
  const [missingCv, setMissingCv] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (coverLetter.trim().length < 50) {
      setError('Votre lettre de motivation doit contenir au moins 50 caractères.')
      return
    }

    setLoading(true)
    setError('')
    setMissingCv(false)

    try {
      const supabase = createClient()
      const { data: { user } } = await supabase.auth.getUser()
      if (!user) {
        router.push(`/login?redirect=/job/${jobId}`)
        return
      }

      const { data: profile } = await supabase
        .from('profiles')
        .select('cv_path')
        .eq('id', user.id)
        .single()

      if (!profile?.cv_path) {
        setMissingCv(true)
        setLoading(false)
        return
      }

      const { error: insertError } = await supabase.from('applications').insert({
        job_id: jobId,
        user_id: user.id,
        cover_letter: coverLetter.trim(),
        cv_path: profile.cv_path,
        status: 'pending',
      })

      if (insertError) {
        if (insertError.code === '23505') {
          setError('Vous avez déjà postulé à cette offre.')
        } else {
          setError(insertError.message)
        }
        return
      }

      setSuccess(true)
      router.refresh()
    } catch {
      setError('Une erreur est survenue. Veuillez réessayer.')
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div className="text-center py-4">
        <div className="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <CheckCircle size={28} className="text-green-600" />
        </div>
        <p className="font-bold text-gray-900 dark:text-white mb-1">Candidature envoyée</p>
        <p className="text-gray-500 text-sm">Vous serez notifié par message.</p>
      </div>
    )
  }

  if (missingCv) {
    return (
      <div className="text-center py-4">
        <div className="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <AlertCircle size={28} className="text-amber-600" />
        </div>
        <p className="font-bold text-gray-900 dark:text-white mb-1">CV manquant</p>
        <p className="text-gray-500 text-sm mb-4">
          Ajoutez un CV à votre profil avant de postuler.
        </p>
        <Link
          href="/user/modifier"
          className="w-full inline-flex items-center justify-center gap-2 py-3 bg-[#C8102E] text-white font-semibold rounded-xl hover:bg-[#a00d24] transition-colors"
        >
          <FileText size={16} />
          Compléter mon profil
        </Link>
      </div>
    )
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <h3 className="font-bold text-gray-900 dark:text-white mb-1">Postuler</h3>
        <p className="text-xs text-gray-500">
          Votre CV actuel sera automatiquement joint.
        </p>
      </div>

      <Textarea
        label="Lettre de motivation"
        placeholder="Présentez votre parcours, vos motivations pour ce poste…"
        rows={6}
        value={coverLetter}
        onChange={e => setCoverLetter(e.target.value)}
        required
      />

      {error && (
        <div className="bg-red-50 border border-red-200 rounded-xl px-3 py-2 text-sm text-red-600">
          {error}
        </div>
      )}

      <Button
        type="submit"
        variant="primary"
        size="lg"
        loading={loading}
        disabled={coverLetter.trim().length < 50}
        className="w-full rounded-xl"
      >
        <Send size={16} />
        Envoyer ma candidature
      </Button>
    </form>
  )
}
