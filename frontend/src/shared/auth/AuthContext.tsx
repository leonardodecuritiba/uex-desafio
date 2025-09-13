import { createContext, useContext, useMemo, useRef, useState } from 'react'
import type { PropsWithChildren, ReactElement } from 'react'
import { isAxiosError } from 'axios'
import { login as apiLogin, logout as apiLogout, me as apiMe } from '../../features/auth/api/auth'

// Cache global simples para evitar chamadas duplicadas em StrictMode (monta/desmonta)
let globalCheckPromise: Promise<void> | null = null
let globalChecked = false
let globalUser: AuthUser = null

export type AuthUser = { id: number; name: string; email: string } | null

type AuthContextValue = {
  user: AuthUser
  isAuthenticated: boolean
  loading: boolean
  checked: boolean
  check: () => Promise<void>
  refresh: () => Promise<void>
  login: (payload: { email: string; password: string }) => Promise<void>
  logout: () => Promise<void>,
  clearUserData: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined)

export function AuthProvider({ children }: PropsWithChildren): ReactElement {
  const [user, setUser] = useState<AuthUser>(null)
  const [loading, setLoading] = useState(false)
  const [checked, setChecked] = useState(false)
  const checkingRef = useRef(false)

  // Checagem preguiçosa: só consulta /me quando necessário
  async function check(): Promise<void> {
    if (checked) return
    if (globalChecked && !checkingRef.current) {
      setUser(globalUser)
      setChecked(true)
      return
    }
    if (globalCheckPromise) {
      setLoading(true)
      await globalCheckPromise
      setUser(globalUser)
      setLoading(false)
      setChecked(true)
      return
    }
    checkingRef.current = true
    setLoading(true)
    globalCheckPromise = (async () => {
      try {
        const res = await apiMe()
        globalUser = res.data
      } catch (err) {
        globalUser = null
      } finally {
        globalChecked = true
      }
    })()
    try {
      await globalCheckPromise
      setUser(globalUser)
    } finally {
      setLoading(false)
      setChecked(true)
      checkingRef.current = false
    }
  }

  async function refresh(): Promise<void> {
    try {
      const res = await apiMe()
      setUser(res.data)
      globalUser = res.data
      globalChecked = true
      setChecked(true)
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 401) setUser(null)
      else setUser(null)
      globalUser = null
      globalChecked = true
      setChecked(true)
    }
  }

  async function login(payload: { email: string; password: string }): Promise<void> {
    await apiLogin(payload)
    await refresh()
  }

  async function logout(): Promise<void> {
    await apiLogout()
    await clearUserData();
  }

  async function clearUserData(): Promise<void> {
    setUser(null)
    globalUser = null
    globalChecked = true
  }

  const value = useMemo<AuthContextValue>(
    () => ({ user, isAuthenticated: !!user, loading, checked, check, refresh, login, logout, clearUserData }),
    [user, loading, checked]
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
