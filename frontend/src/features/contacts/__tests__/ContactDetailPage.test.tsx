import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, waitFor, fireEvent } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import ContactDetailPage from '../pages/ContactDetailPage'
import { api } from '../../../shared/api'

vi.mock('../../../shared/api', async (orig) => {
  const mod = await orig()
  return {
    ...mod,
    api: {
      get: vi.fn(),
      delete: vi.fn(),
    },
  }
})

vi.stubGlobal('navigator', {
  clipboard: {
    writeText: vi.fn().mockResolvedValue(true),
  },
} as any)

// Mock do componente de exclusão para simplificar fluxo F7
vi.mock('../components/ContactDeleteAction', () => ({
  default: ({ onDeleted }: any) => (
    <button onClick={() => onDeleted?.()} aria-label="excluir">
      Excluir
    </button>
  ),
}))

function renderWithProviders(initialPath = '/contacts/1') {
  const qc = new QueryClient()
  return render(
    <QueryClientProvider client={qc}>
      <MemoryRouter initialEntries={[initialPath]}>
        <Routes>
          <Route path="/contacts/:id" element={<ContactDetailPage />} />
          <Route path="/contacts" element={<div>Lista</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>
  )
}

const baseContact = {
  data: {
    id: 1,
    name: 'Maria Silva',
    cpf: '52998224725',
    email: 'maria@example.com',
    phone: '4199',
    address: { localidade: 'Curitiba', uf: 'PR', lat: -25.42, lng: -49.27, logradouro: 'Rua', numero: '123' },
    created_at: '2025-09-11T15:30:00Z',
    updated_at: '2025-09-12T18:12:34Z',
  },
}

describe('ContactDetailPage', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    ;(api.get as any).mockReset()
  })

  it('F1: renderiza skeleton e exibe dados após carregar', async () => {
    ;(api.get as any).mockResolvedValueOnce({ data: baseContact })
    renderWithProviders()
    expect(screen.getByText('Carregando...')).toBeInTheDocument()
    expect(await screen.findByText('Maria Silva')).toBeInTheDocument()
  })

  it('F2/F3/F4: cpf mascarado, mailto/tel e copiar endereço', async () => {
    ;(api.get as any).mockResolvedValueOnce({ data: baseContact })
    renderWithProviders()
    await screen.findByText('Maria Silva')
    expect(screen.getByText('529.982.247-25')).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'maria@example.com' })).toHaveAttribute('href', 'mailto:maria@example.com')
    expect(screen.getByRole('link', { name: '4199' })).toHaveAttribute('href', 'tel:4199')
    // copiar endereço
    fireEvent.click(screen.getByLabelText('copiar endereco'))
    await waitFor(() => expect((navigator as any).clipboard.writeText).toHaveBeenCalled())
  })

  it('F5: sem address exibe aviso', async () => {
    ;(api.get as any).mockResolvedValueOnce({ data: { data: { ...baseContact.data, address: null } } })
    renderWithProviders()
    await screen.findByText('Maria Silva')
    expect(screen.getByText('Endereço não informado')).toBeInTheDocument()
  })

  it('F6: com lat/lng renderiza mapa com um marker', async () => {
    ;(api.get as any).mockResolvedValueOnce({ data: baseContact })
    renderWithProviders()
    await screen.findByText('Maria Silva')
    expect(screen.getByTestId('detail-map-pin')).toBeInTheDocument()
  })

  it('F7: excluir redireciona para lista', async () => {
    ;(api.get as any).mockResolvedValueOnce({ data: baseContact })
    renderWithProviders()
    await screen.findByText('Maria Silva')
    fireEvent.click(screen.getByLabelText('excluir'))
    await screen.findByText('Lista')
  })

  it('F8: erro 404 renderiza página de não encontrado', async () => {
    ;(api.get as any).mockRejectedValueOnce({ response: { status: 404 } })
    renderWithProviders()
    expect(await screen.findByText('Contato não encontrado')).toBeInTheDocument()
  })
})

