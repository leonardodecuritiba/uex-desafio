import { Card, CardContent, Typography, List, ListItem, ListItemText, Button } from '@mui/material'
import { fullAddress } from '../utils/format'
import { useCopy } from '../hooks/useCopy'

type Props = {
  address?: Record<string, any> | null
}

export default function ContactAddressCard({ address }: Props) {
  const { copy } = useCopy()
  const fa = fullAddress(address)
  const items = [
    ['CEP', address?.cep],
    ['Logradouro', address?.logradouro],
    ['Número', address?.numero],
    ['Complemento', address?.complemento],
    ['Bairro', address?.bairro],
    ['Cidade', address?.localidade],
    ['UF', address?.uf],
  ].filter(([, v]) => !!v) as Array<[string, string]>

  return (
    <Card>
      <CardContent>
        <Typography variant="h6" component="h2">
          Endereço
        </Typography>
        {items.length === 0 ? (
          <p>Endereço não informado</p>
        ) : (
          <>
            <List>
              {items.map(([label, val]) => (
                <ListItem key={label}>
                  <ListItemText primary={label} secondary={val} />
                </ListItem>
              ))}
            </List>
            <Button onClick={() => copy(fa)} disabled={!fa} aria-label="copiar endereco">
              Copiar endereço
            </Button>
          </>
        )}
      </CardContent>
    </Card>
  )
}

