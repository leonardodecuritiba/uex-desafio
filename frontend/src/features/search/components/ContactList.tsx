import { List, ListItem, ListItemText, Button } from '@mui/material'
import type { ContactItem } from '../types/contact'
import { maskCpf } from '../utils/cpf'
import { Link } from 'react-router-dom'

type Props = {
  items: ContactItem[]
  selectedId: number | null
  onSelect: (id: number) => void
}

export default function ContactList({ items, selectedId, onSelect }: Props) {
  if (items.length === 0) {
    return <p aria-live="polite">Nenhum contato encontrado</p>
  }
  return (
    <List role="list">
      {items.map((c) => {
        const cityUF = [c.address?.localidade, c.address?.uf].filter(Boolean).join(' / ')
        return (
          <ListItem
            key={c.id}
            role="listitem"
            selected={selectedId === c.id}
            onClick={() => onSelect(c.id)}
            sx={{ cursor: 'pointer' }}
            divider
          >
            <ListItemText
              primary={c.name}
              secondary={`${maskCpf(c.cpf)}${cityUF ? ` • ${cityUF}` : ''}`}
            />
            <Button component={Link} to={`/contacts/${c.id}`} size="small">
              Ver detalhes
            </Button>
          </ListItem>
        )
      })}
    </List>
  )
}
