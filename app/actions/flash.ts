'use server'

import { cookies } from 'next/headers'

/** Doit rester aligné avec `COOKIE_NAME` dans `lib/flash.ts`. */
const COOKIE_NAME = 'bf_flash'

export async function consumeFlashCookie() {
  const store = await cookies()
  store.delete(COOKIE_NAME)
}
