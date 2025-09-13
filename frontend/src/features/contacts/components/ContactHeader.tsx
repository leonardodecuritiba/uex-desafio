import { Button, Card, CardActions, CardContent, Typography } from '@mui/material'
import { useCopy } from '../hooks/useCopy'
import ContactDeleteAction from './ContactDeleteAction'
import { maskCpf, fullAddress } from '../utils/format'
import { useNavigate } from 'react-router-dom'

type Props = {
  contact: {
    id: number
    name: string
    cpf?: string | null
    email?: string | null
    phone?: string | null
    address?: Record<string, any> | null
  }
}

export default function ContactHeader({ contact }: Props) {
  const { copy } = useCopy()
  const navigate = useNavigate()

  const handleCopyAll = async () => {
    const text = [
      contact.name,
      `CPF: ${maskCpf(contact.cpf)}`,
      contact.email ? `E-mail: ${contact.email}` : null,
      contact.phone ? `Telefone: ${contact.phone}` : null,
      fullAddress(contact.address) ? `Endereço: ${fullAddress(contact.address)}` : null,
    ]
      .filter(Boolean)
      .join('\n')
    await copy(text)
  }

  return (
    <Card>
      <CardContent>
        <Typography variant="h5" component="h2">
          {contact.name}
        </Typography>
      </CardContent>
      <CardActions>
        <Button variant="outlined" size="small" onClick={() => navigate(`/contacts/${contact.id}/edit`)}>
          Editar
        </Button>
        <ContactDeleteAction id={contact.id} variant="detail" onDeleted={() => navigate('/contacts')} />
        <Button variant="contained" size="small" onClick={handleCopyAll} aria-label="copiar tudo">
          Copiar tudo
        </Button>
      </CardActions>
    </Card>
  )
}

