import { Card, CardContent, IconButton, List, ListItem, ListItemText, Tooltip, Typography } from '@mui/material'
import ContentCopyIcon from '@mui/icons-material/ContentCopy'
import { useCopy } from '../hooks/useCopy'
import { maskCpf, buildMailto, buildTel, formatDateISO } from '../utils/format'

type Props = {
  cpf?: string | null
  email?: string | null
  phone?: string | null
  createdAt?: string | null
  updatedAt?: string | null
}

export default function ContactInfoCard({ cpf, email, phone, createdAt, updatedAt }: Props) {
  const { copy } = useCopy()
  const items: Array<{ label: string; value: string; copyValue?: string; href?: string | null }> = [
    { label: 'CPF', value: maskCpf(cpf), copyValue: cpf || '' },
    { label: 'E-mail', value: email || '-', copyValue: email || '', href: buildMailto(email) },
    { label: 'Telefone', value: phone || '-', copyValue: phone || '', href: buildTel(phone) },
    { label: 'Criado em', value: formatDateISO(createdAt || null) },
    { label: 'Atualizado em', value: formatDateISO(updatedAt || null) },
  ]

  return (
    <Card>
      <CardContent>
        <Typography variant="h6" component="h2">
          Informações
        </Typography>
        <List>
          {items.map((it) => (
            <ListItem key={it.label} secondaryAction={it.copyValue !== undefined && (
                <Tooltip title={`Copiar ${it.label}`}>
                  <IconButton aria-label={`copiar ${it.label}`} onClick={() => copy(it.copyValue || '')}>
                    <ContentCopyIcon fontSize="small" />
                  </IconButton>
                </Tooltip>
              )}
            >
              <ListItemText
                primary={it.label}
                secondary={
                  it.href ? (
                    <a href={it.href}>{it.value}</a>
                  ) : (
                    it.value
                  )
                }
              />
            </ListItem>
          ))}
        </List>
      </CardContent>
    </Card>
  )
}

