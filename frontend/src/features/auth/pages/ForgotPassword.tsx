import { useState } from 'react'
import { Container, Typography, Box, Paper, TextField, Button, Alert, Stack, Link as MLink } from '@mui/material'
import { z } from 'zod'
import { forgotPassword } from '../api/auth'
import { isAxiosError } from 'axios'
import { Link } from 'react-router-dom'

const schema = z.object({ email: z.string().email('E-mail inválido') })

export default function ForgotPasswordPage() {
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<string[]>([])
  const [success, setSuccess] = useState<string | null>(null)

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setErrors([])
    setSuccess(null)
    const formEl = e.currentTarget
    const form = new FormData(formEl)
    const payload = { email: String(form.get('email') || '') }
    const parsed = schema.safeParse(payload)
    if (!parsed.success) {
      setErrors(parsed.error.issues.map((i) => i.message))
      return
    }
    try {
      setLoading(true)
      const response = await forgotPassword(payload);
      setSuccess(response.message);
      formEl?.reset();
    } catch (err) {
      type ApiError = { errors?: { field: string; message: string }[] }
      if (isAxiosError(err)) {
        const data = err.response?.data as ApiError | undefined
        const errs = data?.errors
        if (errs?.length) setErrors(errs.map((x) => x.message))
        else setErrors(['Falha ao solicitar redefinição.'])
      } else {
        setErrors(['Falha ao solicitar redefinição.'])
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
            Esqueci minha senha
          </Typography>
          <form onSubmit={onSubmit} noValidate>
            <Stack spacing={2}>
              {success && <Alert severity="success">{success}</Alert>}
              {errors.map((m, i) => (
                <Alert key={i} severity="error">{m}</Alert>
              ))}
              <TextField name="email" label="E-mail" type="email" required fullWidth />
              <Button type="submit" variant="contained" disabled={loading}>
                {loading ? 'Enviando...' : 'Enviar e-mail de redefinição'}
              </Button>
              <Typography variant="body2">
                Lembrou sua senha? <MLink component={Link} to="/login">Entrar</MLink>
              </Typography>
            </Stack>
          </form>
        </Box>
      </Paper>
    </Container>
  )
}

