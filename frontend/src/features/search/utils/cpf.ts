export function maskCpf(cpf?: string | null): string {
  if (!cpf) return '***.***.***-**'
  const digits = cpf.replace(/\D+/g, '').padStart(11, '0').slice(-11)
  return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

export function unmaskCpf(value: string): string {
  return value.replace(/\D+/g, '')
}

