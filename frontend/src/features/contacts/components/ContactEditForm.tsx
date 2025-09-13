/* eslint-disable @typescript-eslint/no-explicit-any */
import { useEffect, useMemo, useRef, useState } from 'react'
import { contactSchema, type ContactFormValues } from '../validation/contactSchema'
import { Grid, Card, CardContent, Typography, TextField, Button, Alert, Stack } from '@mui/material'
import { useCepLookup } from '../hooks/useCepLookup'

type Props = {
  initialValues: ContactFormValues & { id: number; updated_at?: string | null }
  onSubmit: (patch: Partial<ContactFormValues> & { version?: string }) => Promise<void> | void
  submitting?: boolean
  backendErrors?: { field: string; message: string }[] | null
  onCancel?: () => void
}

function buildPatch(initial: ContactFormValues, current: ContactFormValues): Partial<ContactFormValues> {
  const patch: any = {}
  // escalares
  ;(['name', 'cpf', 'email', 'phone'] as const).forEach((k) => {
    const a = (initial as any)[k] ?? ''
    const b = (current as any)[k] ?? ''
    if (a !== b) {
      if (b === '' || b === undefined) {
        // para campos opcionais fora de address, omitimos para evitar nulls indevidos
        // (remoção explícita é requisito apenas para subcampos de address)
      } else {
        patch[k] = b
      }
    }
  })

  // address (merge com remoção por null)
  const ia = initial.address || {}
  const ca = current.address || {}
  const addressPatch: any = {}
  const keys = new Set<string>([...Object.keys(ia), ...Object.keys(ca)])
  keys.forEach((k) => {
    const av = (ia as any)[k]
    const bv = (ca as any)[k]
    if (av !== bv) {
      if (bv === '' || bv === undefined) addressPatch[k] = null
      else addressPatch[k] = bv
    }
  })
  if (Object.keys(addressPatch).length > 0) patch.address = addressPatch

  return patch
}

export default function ContactEditForm({ initialValues, onSubmit, submitting = false, backendErrors, onCancel }: Props) {
  const [values, setValues] = useState<ContactFormValues>({ ...initialValues })
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [bannerError, setBannerError] = useState<string | null>(null)
  const firstErrorRef = useRef<HTMLInputElement | null>(null)
  const { lookup, loading: loadingCep, error: cepError, setError: setCepError } = useCepLookup()

  const canLookupCep = useMemo(() => (values.address?.cep || '').replace(/\D+/g, '').length === 8, [values.address?.cep])

  useEffect(() => {
    if (backendErrors && backendErrors.length) {
      const e: Record<string, string> = {}
      backendErrors.forEach((err) => {
        e[err.field] = err.message
      })
      setErrors((prev) => ({ ...prev, ...e }))
      setBannerError('Corrija os campos destacados e tente novamente.')
    }
  }, [backendErrors])

  function setField(path: string, value: any) {
    setValues((prev) => {
      const next: any = { ...prev }
      if (path.startsWith('address.')) {
        next.address = { ...(prev.address || {}) }
        const k = path.split('.')[1]
        next.address[k] = value
      } else {
        ;(next as any)[path] = value
      }
      return next
    })
  }

  async function handleCep() {
    if (!canLookupCep) return
    const cep = (values.address?.cep || '').replace(/\D+/g, '')
    const data = await lookup(cep)
    if (data) {
      setValues((prev) => ({
        ...prev,
        address: {
          ...(prev.address || {}),
          cep: data.cep?.replace(/\D+/g, '') || prev.address?.cep,
          logradouro: data.logradouro ?? prev.address?.logradouro,
          bairro: data.bairro ?? prev.address?.bairro,
          localidade: data.localidade ?? prev.address?.localidade,
          uf: data.uf ?? prev.address?.uf,
        },
      }))
      setCepError(null)
    }
  }

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setBannerError(null)
    setErrors({})

    // valida apenas o que estiver preenchido ou obrigatório
    const parsed = contactSchema.safeParse(values)
    if (!parsed.success) {
      const e: Record<string, string> = {}
      parsed.error.issues.forEach((i) => {
        const key = i.path.join('.')
        e[key] = i.message
      })
      setErrors(e)
      firstErrorRef.current?.focus()
      return
    }

    try {
      const patch = buildPatch(initialValues, values)
      if (initialValues.updated_at) (patch as any).version = initialValues.updated_at
      await onSubmit(patch)
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (err) {
      setBannerError('Não foi possível atualizar. Tente novamente.')
    }
  }

  return (
    <form onSubmit={submit} noValidate>
      <Stack spacing={2}>
        {bannerError && <Alert severity="error">{bannerError}</Alert>}
        <Card>
          <CardContent>
            <Typography variant="h6" sx={{ mb: 2 }}>
              Dados Pessoais
            </Typography>
            <Grid container spacing={2}>
              <Grid>
                <TextField
                  fullWidth
                  label="Nome"
                  value={values.name || ''}
                  onChange={(e) => setField('name', e.target.value)}
                  error={!!errors['name']}
                  helperText={errors['name']}
                  inputRef={errors['name'] ? firstErrorRef : undefined}
                  required
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="CPF"
                  value={values.cpf || ''}
                  onChange={(e) => setField('cpf', e.target.value)}
                  error={!!errors['cpf']}
                  helperText={errors['cpf']}
                  required
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="E-mail"
                  type="email"
                  value={values.email || ''}
                  onChange={(e) => setField('email', e.target.value)}
                  error={!!errors['email']}
                  helperText={errors['email']}
                  required
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="Telefone"
                  value={values.phone || ''}
                  onChange={(e) => setField('phone', e.target.value)}
                  error={!!errors['phone']}
                  helperText={errors['phone']}
                  required
                />
              </Grid>
            </Grid>
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <Typography variant="h6" sx={{ mb: 2 }}>
              Endereço
            </Typography>
            <Grid container spacing={2}>
              <Grid>
                <TextField
                  fullWidth
                  label="UF"
                  value={values.address?.uf || ''}
                  onChange={(e) => setField('address.uf', e.target.value.toUpperCase())}
                  error={!!errors['address.uf']}
                  helperText={errors['address.uf']}
                  required
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="Cidade"
                  value={values.address?.localidade || ''}
                  onChange={(e) => setField('address.localidade', e.target.value)}
                  error={!!errors['address.localidade']}
                  helperText={errors['address.localidade']}
                  required
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="CEP"
                  value={values.address?.cep || ''}
                  onChange={(e) => setField('address.cep', e.target.value)}
                  error={!!errors['address.cep'] || !!cepError}
                  helperText={errors['address.cep'] || cepError || ''}
                  required
                />
              </Grid>
              <Grid>
                <Button
                  variant="outlined"
                  onClick={handleCep}
                  disabled={!canLookupCep || loadingCep}
                  sx={{ mt: { xs: 1, md: 0.5 } }}
                >
                  {loadingCep ? 'Buscando...' : 'Buscar CEP'}
                </Button>
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="Logradouro"
                  value={values.address?.logradouro || ''}
                  onChange={(e) => setField('address.logradouro', e.target.value)}
                  error={!!errors['address.logradouro']}
                  helperText={errors['address.logradouro']}
                  required
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="Número"
                  value={values.address?.numero || ''}
                  onChange={(e) => setField('address.numero', e.target.value)}
                  error={!!errors['address.numero']}
                  helperText={errors['address.numero']}
                  required
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="Complemento"
                  value={values.address?.complemento ?? ''}
                  onChange={(e) => setField('address.complemento', e.target.value)}
                  error={!!errors['address.complemento']}
                  helperText={errors['address.complemento']}
                />
              </Grid>
              <Grid>
                <TextField
                  fullWidth
                  label="Bairro"
                  value={values.address?.bairro || ''}
                  onChange={(e) => setField('address.bairro', e.target.value)}
                  error={!!errors['address.bairro']}
                  helperText={errors['address.bairro']}
                />
              </Grid>
            </Grid>
          </CardContent>
        </Card>

        <Stack direction="row" spacing={2}>
          <Button type="submit" variant="contained" disabled={submitting}>
            {submitting ? 'Salvando...' : 'Salvar alterações'}
          </Button>
          {onCancel && (
            <Button type="button" variant="text" onClick={onCancel} disabled={submitting}>
              Cancelar
            </Button>
          )}
        </Stack>
      </Stack>
    </form>
  )
}
