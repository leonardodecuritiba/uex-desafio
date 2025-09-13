import { Navigate } from 'react-router-dom'
import type { PropsWithChildren, ReactElement } from 'react'
import { useEffect } from 'react'
import { useAuth } from './AuthContext'

// Rotas de convidado: se autenticado, redireciona para /me
export default function GuestRoute({ children }: PropsWithChildren): ReactElement {
  const { isAuthenticated, loading, checked, check } = useAuth()
  useEffect(() => {
    if (!checked) void check()
  }, [checked, check])
  if (!checked || loading) return <></>
  if (isAuthenticated) return <Navigate to="/me" replace />
  return <>{children}</>
}
