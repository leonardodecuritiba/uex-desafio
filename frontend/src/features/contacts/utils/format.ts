export function maskCpf(cpf?: string | null): string {
  if (!cpf) return '***.***.***-**'
  const digits = cpf.replace(/\D+/g, '').padStart(11, '0').slice(-11)
  return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

export function formatDateISO(dt?: string | null): string {
  if (!dt) return '-'
  try {
    const d = new Date(dt)
    return d.toISOString()
  } catch {
    return String(dt)
  }
}

export function buildMailto(email?: string | null): string | null {
  if (!email) return null
  return `mailto:${email}`
}

export function buildTel(phone?: string | null): string | null {
  if (!phone) return null
  return `tel:${phone}`
}

export function fullAddress(address?: Record<string, any> | null): string {
  if (!address) return ''
  const parts = [
    address.logradouro,
    address.numero,
    address.complemento,
    address.bairro,
    address.localidade,
    address.uf,
    address.cep,
  ].filter(Boolean)
  return parts.join(', ')
}

