import { useState } from 'react'
import { z } from 'zod'
import { TextField, Button, Stack, Alert } from '@mui/material'
import { register } from '../api/register'
import { isAxiosError } from 'axios'
import type { RegisterPayload } from '../api/register'

const schema = z.object({
  name: z.string().min(2, 'Nome muito curto'),
  email: z.string().email('E-mail inválido'),
  password: z.string().min(8, 'Senha deve ter ao menos 8 caracteres'),
  password_confirmation: z.string(),
}).refine((d) => d.password === d.password_confirmation, {
  message: 'Confirmação de senha não confere',
  path: ['password_confirmation'],
})

export function RegisterForm() {
  const [loading, setLoading] = useState(false)
  const [serverErrors, setServerErrors] = useState<string[]>([])
  const [successMsg, setSuccessMsg] = useState<string | null>(null)

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setServerErrors([])
    setSuccessMsg(null)
    const formEl = e.currentTarget
    const form = new FormData(formEl)
    const payload: RegisterPayload = {
      name: String(form.get('name') || ''),
      email: String(form.get('email') || ''),
      password: String(form.get('password') || ''),
      password_confirmation: String(form.get('password_confirmation') || ''),
    }
    const parsed = schema.safeParse(payload)
    if (!parsed.success) {
      setServerErrors(parsed.error.issues.map((e) => e.message))
      return
    }
    try {
      setLoading(true)
      await register(payload)
      setSuccessMsg('Conta criada com sucesso.')
      formEl?.reset()
    } catch (err: unknown) {
      setSuccessMsg(null);
      type ApiError = { errors?: { field: string; message: string }[] }
      if (isAxiosError(err)) {
        const data = err.response?.data as ApiError | undefined
        const errs = data?.errors
        if (errs?.length) {
          setServerErrors(errs.map((x) => x.message))
        } else {
          setServerErrors(['Falha ao criar conta.'])
        }
      } else {
        setServerErrors(['Falha ao criar conta.'])
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <form onSubmit={onSubmit} noValidate autoComplete="off">
      <Stack spacing={2}>
        {successMsg && (
          <Alert severity="success">{successMsg}</Alert>
        )}
        {serverErrors.map((m, i) => (
          <Alert key={i} severity="error">
            {m}
          </Alert>
        ))}
        <TextField name="name" label="Nome" required inputProps={{ minLength: 2, maxLength: 100 }} />
        <TextField name="email" label="E-mail" type="email" required />
        <TextField name="password" label="Senha" type="password" required />
        <TextField name="password_confirmation" label="Confirmar Senha" type="password" required />
        <Button variant="contained" type="submit" disabled={loading}>
          {loading ? 'Enviando...' : 'Criar conta'}
        </Button>
      </Stack>
    </form>
  )
}
