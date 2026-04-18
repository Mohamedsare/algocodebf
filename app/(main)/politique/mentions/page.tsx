import type { Metadata } from 'next'
import { readPolicyHtml } from '@/lib/policy-content'

export const metadata: Metadata = {
  title: 'Mentions légales',
  description: 'Informations légales et éditeur du site AlgoCodeBF.',
}

export default function LegalPage() {
  const html = readPolicyHtml('legal')
  return <div className="policy-page" dangerouslySetInnerHTML={{ __html: html }} />
}
