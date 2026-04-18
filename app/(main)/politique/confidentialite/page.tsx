import type { Metadata } from 'next'
import { readPolicyHtml } from '@/lib/policy-content'

export const metadata: Metadata = {
  title: 'Politique de confidentialité',
  description:
    "Comment AlgoCodeBF collecte, utilise et protège vos données personnelles sur la plateforme.",
}

export default function PrivacyPage() {
  const html = readPolicyHtml('privacy')
  return <div className="policy-page" dangerouslySetInnerHTML={{ __html: html }} />
}
