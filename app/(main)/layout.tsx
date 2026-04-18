import { Header } from '@/components/layout/header'
import { Footer } from '@/components/layout/footer'
import { FlashMessages } from '@/components/layout/flash-messages'
import { getProfile } from '@/lib/supabase/server'

export default async function MainLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const profile = await getProfile()

  return (
    <>
      <Header profile={profile} />
      <FlashMessages />
      <main className="main-content">{children}</main>
      <Footer />
    </>
  )
}
