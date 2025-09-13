import { useMemo, useState } from 'react'
import { Container, Typography, Box, Paper, TextField, Button, Alert, Stack } from '@mui/material'
import { z } from 'zod'
import { resetPassword } from '../api/auth'
import { isAxiosError } from 'axios'
import { useSearchParams } from 'react-router-dom'

const schema = z
  .object({
    token: z.string().min(1),
    email: z.string().email('E-mail inválido'),
    password: z.string().min(8, 'Senha deve ter ao menos 8 caracteres'),
    password_confirmation: z.string(),
  })
  .refine((d) => d.password === d.password_confirmation, {
    message: 'Confirmação de senha não confere',
    path: ['password_confirmation'],
  })

export default function ResetPasswordPage() {
  const [search] = useSearchParams()
  const token = search.get('token') || ''
  const emailFromLink = search.get('email') || ''
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<string[]>([])
  const [success, setSuccess] = useState<string | null>(null)

  const defaultValues = useMemo(() => ({ token, email: emailFromLink }), [token, emailFromLink])

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setErrors([])
    setSuccess(null)
    const formEl = e.currentTarget
    const form = new FormData(formEl)
    const payload = {
      token: String(form.get('token') || ''),
      email: String(form.get('email') || ''),
      password: String(form.get('password') || ''),
      password_confirmation: String(form.get('password_confirmation') || ''),
    }
    const parsed = schema.safeParse(payload)
    if (!parsed.success) {
      setErrors(parsed.error.issues.map((i) => i.message))
      return
    }
    try {
      setLoading(true)
      const response = await resetPassword(payload);
      setSuccess(response.message);
      formEl?.reset();
    } catch (err) {
      type ApiError = { errors?: { field: string; message: string }[] }
      if (isAxiosError(err)) {
        const data = err.response?.data as ApiError | undefined
        const errs = data?.errors
        if (errs?.length) setErrors(errs.map((x) => x.message))
        else setErrors(['Falha ao redefinir senha.'])
      } else {
        setErrors(['Falha ao redefinir senha.'])
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
            Redefinir senha
          </Typography>
          <form onSubmit={onSubmit} noValidate>
            <Stack spacing={2}>
              {success && <Alert severity="success">{success}</Alert>}
              {errors.map((m, i) => (
                <Alert key={i} severity="error">{m}</Alert>
              ))}
              <input type="hidden" name="token" defaultValue={defaultValues.token} />
              <TextField name="email" label="E-mail" type="email" required fullWidth defaultValue={defaultValues.email} />
              <TextField name="password" label="Nova senha" type="password" required fullWidth />
              <TextField name="password_confirmation" label="Confirmar nova senha" type="password" required fullWidth />
              <Button type="submit" variant="contained" disabled={loading}>
                {loading ? 'Enviando...' : 'Alterar senha'}
              </Button>
            </Stack>
          </form>
        </Box>
      </Paper>
    </Container>
  )
}

