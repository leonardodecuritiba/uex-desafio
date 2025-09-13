import { useState } from 'react'
import { Container, Typography, Box, Paper, TextField, Button, Alert, Stack, Link as MLink } from '@mui/material'
import { z } from 'zod'
import { isAxiosError } from 'axios'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../../../shared/auth/AuthContext'

const schema = z.object({
  email: z.string().email('E-mail inválido'),
  password: z.string().min(8, 'Senha deve ter ao menos 8 caracteres'),
})

export default function LoginPage() {
  const navigate = useNavigate()
  const { login } = useAuth()
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<string[]>([])
  const [success, setSuccess] = useState<string | null>(null)

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setErrors([])
    setSuccess(null)
    const form = new FormData(e.currentTarget)
    const payload = {
      email: String(form.get('email') || ''),
      password: String(form.get('password') || ''),
    }
    const parsed = schema.safeParse(payload)
    if (!parsed.success) {
      setErrors(parsed.error.issues.map((i) => i.message))
      return
    }
    try {
      setLoading(true)
      await login(payload)
      setSuccess('Login realizado com sucesso.')
      navigate('/me')
    } catch (err) {
      if (isAxiosError(err)) {
        const status = err.response?.status
        if (status === 401) setErrors(['Credenciais inválidas.'])
        else setErrors(['Falha ao entrar.'])
      } else {
        setErrors(['Falha ao entrar.'])
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <Container maxWidth="sm" sx={{ py: 6 }}>
      <Paper elevation={3} sx={{ p: 3 }}>
        <Box display="flex" flexDirection="column" gap={2}>
          <Typography variant="h4" component="h1">
            Entrar
          </Typography>
          <form onSubmit={onSubmit} noValidate>
            <Stack spacing={2}>
              {success && <Alert severity="success">{success}</Alert>}
              {errors.map((m, i) => (
                <Alert key={i} severity="error">{m}</Alert>
              ))}
              <TextField name="email" label="E-mail" type="email" required fullWidth />
              <TextField name="password" label="Senha" type="password" required fullWidth />
              <Button type="submit" variant="contained" disabled={loading}>
                {loading ? 'Entrando...' : 'Entrar'}
              </Button>
              <Typography variant="body2">
                <MLink component={Link} to="/forgot-password">Esqueci minha senha</MLink> ·{' '}
                <MLink component={Link} to="/register">Criar conta</MLink>
              </Typography>
            </Stack>
          </form>
        </Box>
      </Paper>
    </Container>
  )
}
