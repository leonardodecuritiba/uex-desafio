import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import ContactForm from '../../contacts/components/ContactForm'

describe('ContactForm', () => {
  beforeEach(() => {
    vi.resetModules()
  })

  it('blocks submit when CPF is invalid', async () => {
    const onSubmit = vi.fn()
    render(<ContactForm onSubmit={onSubmit} />)

    fireEvent.change(screen.getByLabelText(/Nome/i), { target: { value: 'João' } })
    fireEvent.change(screen.getByLabelText(/CPF/i), { target: { value: '12345678900' } })

    fireEvent.click(screen.getByRole('button', { name: /Salvar/i }))

    expect(await screen.findByText(/CPF inválido/i)).toBeInTheDocument()
    expect(onSubmit).not.toHaveBeenCalled()
  })
})

