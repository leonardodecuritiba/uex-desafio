/* eslint-disable @typescript-eslint/no-explicit-any */
import { useNavigate, useParams } from 'react-router-dom'
import { Container, Paper, Snackbar, Alert, Typography, Stack } from '@mui/material'
import ContactEditForm from '../components/ContactEditForm'
import { useContact } from '../hooks/useContact'
import { useEditContact } from '../hooks/useEditContact'
import ContactDeleteAction from '../components/ContactDeleteAction'

export default function EditContactPage() {
  const params = useParams()
  const id = Number(params.id)
  const navigate = useNavigate()
  const { data, isLoading, isError } = useContact(isFinite(id) ? id : undefined)
  const mutation = useEditContact(id)

  async function onSubmit(patch: any) {
    await mutation.mutateAsync(patch)
  }

  if (!isFinite(id)) return <Typography sx={{ p: 2 }}>ID inválido</Typography>
  if (isLoading) return <Typography sx={{ p: 2 }}>Carregando...</Typography>
  if (isError || !data?.data) return <Typography sx={{ p: 2 }}>Contato não encontrado</Typography>

  const initial = {
    id: data.data.id,
    name: data.data.name,
    cpf: data.data.cpf ?? undefined,
    email: data.data.email ?? undefined,
    phone: data.data.phone ?? undefined,
    address: data.data.address ?? undefined,
    updated_at: data.data.updated_at ?? undefined,
  }

  return (
    <Container maxWidth="md" sx={{ py: 4 }}>
      <Paper sx={{ p: 3 }} elevation={3}>
        <Stack direction="row" justifyContent="space-between" alignItems="center" sx={{ mb: 2 }}>
          <Typography variant="h5">Editar contato</Typography>
          <ContactDeleteAction id={id} variant="detail" onDeleted={() => navigate('/me')} />
        </Stack>
        <ContactEditForm
          initialValues={initial as any}
          onSubmit={onSubmit}
          submitting={mutation.isPending}
          backendErrors={mutation.error?.errors ?? null}
          onCancel={() => navigate(-1)}
        />
      </Paper>
      <Snackbar open={mutation.isSuccess} autoHideDuration={2000} onClose={() => {}}>
        <Alert severity="success" sx={{ width: '100%' }}>
          Contato atualizado
        </Alert>
      </Snackbar>
      {/* Conflito de versão 409: exibir banner simples solicitando recarregar */}
      {mutation.error && !mutation.error.errors && (
        <Snackbar open autoHideDuration={4000} onClose={() => {}}>
          <Alert severity="error" sx={{ width: '100%' }}>
            {mutation.error.message || 'Não foi possível atualizar. Tente novamente.'}
          </Alert>
        </Snackbar>
      )}
    </Container>
  )
}
