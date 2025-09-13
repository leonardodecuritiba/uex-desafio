import { useState } from 'react'
import { Container, Typography, Box, Paper, Button, Stack, TextField, FormControlLabel, Checkbox, Alert } from '@mui/material'
import { deleteAccount } from '../api/account'
import { isAxiosError } from 'axios'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../../../shared/auth/AuthContext'

export default function DeleteAccountPage() {
  const navigate = useNavigate()
  const { clearUserData } = useAuth()
  const [password, setPassword] = useState('')
  const [confirm, setConfirm] = useState(false)
  const [serverErrors, setServerErrors] = useState<string[]>([])
  const [successMsg, setSuccessMsg] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setServerErrors([])
    setSuccessMsg(null)
    if (!confirm) {
      setServerErrors(['Confirme que entende as consequências.'])
      return
    }
    try {
      setLoading(true)
      const res = await deleteAccount({ password })
      setSuccessMsg(res.message)
      // Em app real: limpar estado de auth e redirecionar para login
      setServerErrors([])
      setSuccessMsg(null)
      await clearUserData()
      navigate('/login')
    } catch (err: unknown) {
      setSuccessMsg(null);
      type ApiError = { message: string }
      if (isAxiosError(err)) {
        const data = err.response?.data as ApiError | undefined
        if (data?.message) {
          setServerErrors([data.message])
        } else {
          setServerErrors(['Falha ao excluir conta.'])
        }
      } else {
        setServerErrors(['Falha ao excluir conta.'])
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
              Excluir conta
            </Typography>
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
                  <TextField name="password" label="Senha atual" type="password" required onChange={(e) => setPassword(e.target.value)} />
                  <FormControlLabel control={
                    <Checkbox
                      name="confirm"
                      checked={confirm}
                      onChange={(e) => setConfirm(e.target.checked)}
                    />
                    } label="Entendo as consequências da exclusão definitiva."
                  />
                  <Button variant="contained" type="submit" disabled={!password || !confirm || loading}>
                    {loading ? 'Excluindo...' : 'Excluir conta'}
                  </Button>
              </Stack>
            </form>
          </Box>
        </Paper>
      </Container>
  )
}

