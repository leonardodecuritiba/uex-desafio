import { useMemo, useState } from 'react'
import { Container, Typography, Box, Paper, Button, Alert, Stack } from '@mui/material'
import { isAxiosError } from 'axios'
import { Link, useNavigate } from 'react-router-dom'
import type { MeResponse } from '../api/auth'
import { useAuth } from '../../../shared/auth/AuthContext'

/**
 * ProfilePage
 * Página simples que busca os dados do usuário autenticado via /api/auth/me
 * e oferece um botão para realizar logout via /api/auth/logout.
 * - Exibe nome e e-mail do usuário.
 * - Em caso de não autenticado (401), sugere ir para /login.
 * - Após logout, redireciona para /login.
 */
export default function ProfilePage() {
  const navigate = useNavigate()
  const { logout } = useAuth()
  const { user: authUser, checked } = useAuth()
  const [loading, setLoading] = useState(false)
  const user = useMemo<MeResponse['data'] | null>(() => authUser, [authUser])
  const [errors, setErrors] = useState<string[]>([])

  // Dados vêm do AuthProvider (via ProtectedRoute já garantiu a checagem)
  // Mantemos states locais apenas para mensagens de erro não críticas.

  async function onLogout() {
    try {
      setErrors([])
      await logout()
      navigate('/login')
    } catch (err) {
      if (isAxiosError(err)) {
        setErrors(['Falha ao sair da conta.'])
      } else {
        setErrors(['Falha ao sair da conta.'])
      }
    }
  }

  return (
    <Container maxWidth="sm" sx={{ py: 6 }}>
      <Paper elevation={3} sx={{ p: 3 }}>
        <Box display="flex" flexDirection="column" gap={2}>
          <Typography variant="h4" component="h1">
            Meu perfil
          </Typography>
          <Stack spacing={2}>
            {errors.map((m, i) => (
              <Alert key={i} severity="error">{m}</Alert>
            ))}
            {loading && <Typography>Carregando...</Typography>}
            {!loading && user && (
              <Box>
                <Typography variant="subtitle1">Nome</Typography>
                <Typography variant="body1" sx={{ mb: 2 }}>{user.name}</Typography>
                <Typography variant="subtitle1">E-mail</Typography>
                <Typography variant="body1" sx={{ mb: 2 }}>{user.email}</Typography>
                <Stack direction="row" spacing={2}>
                  <Button variant="contained" color="primary" onClick={onLogout}>
                    Sair
                  </Button>
                  <Button
                    variant="outlined"
                    color="error"
                    onClick={() => navigate('/account/delete')}
                  >
                    Excluir conta
                  </Button>
                </Stack>
              </Box>
            )}
            {!loading && !user && (
              <Typography>
                Não autenticado. Ir para <Link to="/login">login</Link>.
              </Typography>
            )}
          </Stack>
        </Box>
      </Paper>
    </Container>
  )
}
