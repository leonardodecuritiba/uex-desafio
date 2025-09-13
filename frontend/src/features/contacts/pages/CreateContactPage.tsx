import { useNavigate } from 'react-router-dom'
import { Container, Paper, Snackbar, Alert } from '@mui/material'
import ContactForm from '../components/ContactForm'
import { useCreateContact } from '../hooks/useCreateContact'

export default function CreateContactPage() {
  const navigate = useNavigate()
  const mutation = useCreateContact()

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  async function onSubmit(values: any) {
    await mutation.mutateAsync(values)
    navigate('/me')
  }

  return (
    <Container maxWidth="md" sx={{ py: 4 }}>
      <Paper sx={{ p: 3 }} elevation={3}>
        <ContactForm onSubmit={onSubmit} submitting={mutation.isPending} backendErrors={mutation.error?.errors ?? null} />
      </Paper>
      <Snackbar open={mutation.isSuccess} autoHideDuration={2000} onClose={() => {}}>
        <Alert severity="success" sx={{ width: '100%' }}>
          Contato criado
        </Alert>
      </Snackbar>
    </Container>
  )
}

