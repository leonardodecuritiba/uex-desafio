import { z } from 'zod'

export function isValidCpf(input?: string | null): boolean {
  if (!input) return false
  const cpf = (input || '').replace(/\D+/g, '')
  if (cpf.length !== 11) return false
  if (/^(\d)\1{10}$/.test(cpf)) return false
  let d1 = 0
  for (let i = 0; i < 9; i++) d1 += parseInt(cpf[i]) * (10 - i)
  d1 = ((10 * d1) % 11) % 10
  if (d1 !== parseInt(cpf[9])) return false
  let d2 = 0
  for (let i = 0; i < 10; i++) d2 += parseInt(cpf[i]) * (11 - i)
  d2 = ((10 * d2) % 11) % 10
  return d2 === parseInt(cpf[10])
}

export const addressSchema = z.object({
  cep: z.string().regex(/^\d{8}$/, 'CEP deve conter 8 dígitos.'),
  logradouro: z.string().min(2).max(150),
  numero: z.string().min(1).max(20),
  complemento: z.string().max(150).optional().or(z.literal('')).transform((v) => (v ? v : undefined)),
  bairro: z.string().min(2).max(100),
  localidade: z.string().min(2).max(120),
  uf: z.string().length(2, 'UF deve ter 2 caracteres.'),
})

export const contactSchema = z.object({
  name: z.string().min(2, 'Informe ao menos 2 caracteres.').max(120),
  cpf: z.string().regex(/^\d{11}$/, 'CPF deve conter 11 dígitos.').refine((v) => isValidCpf(v), 'CPF inválido.'),
  email: z.string().email('E-mail inválido.').optional().or(z.literal('').transform(() => undefined)),
  phone: z.string().max(20).optional().or(z.literal('').transform(() => undefined)),
  address: addressSchema,
})

export type ContactFormValues = z.infer<typeof contactSchema>
