import type { Metadata } from 'next'
import { readPolicyHtml } from '@/lib/policy-content'

export const metadata: Metadata = {
  title: "Conditions d'utilisation",
  description:
    "Les règles d'utilisation, obligations et responsabilités sur la plateforme AlgoCodeBF.",
}

export default function TermsPage() {
  const html = readPolicyHtml('terms')
  return <div className="policy-page" dangerouslySetInnerHTML={{ __html: html }} />
}
