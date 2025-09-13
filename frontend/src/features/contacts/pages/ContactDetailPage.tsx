import { useParams, useNavigate } from 'react-router-dom'
import { useContact } from '../hooks/useContact'
import ContactHeader from '../components/ContactHeader'
import ContactInfoCard from '../components/ContactInfoCard'
import ContactAddressCard from '../components/ContactAddressCard'
import ContactMapCard from '../components/ContactMapCard'

export default function ContactDetailPage() {
  const params = useParams()
  const id = Number(params.id)
  const navigate = useNavigate()
  const { data, isLoading, isError, error, refetch } = useContact(Number.isFinite(id) ? id : undefined)

  if (isLoading) return <p>Carregando...</p>

  if (isError) {
    const anyErr = error as any
    const status = anyErr?.response?.status
    if (status === 404) {
      return (
        <div>
          <h2>Contato não encontrado</h2>
          <button onClick={() => navigate('/contacts')}>Voltar</button>
        </div>
      )
    }
    return (
      <div role="alert">
        <p>Não foi possível carregar. Tente novamente</p>
        <button onClick={() => refetch()}>Recarregar</button>
      </div>
    )
  }

  const contact = data?.data
  if (!contact) return null

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
      <div style={{ display: 'grid', gap: 12 }}>
        <ContactHeader contact={contact} />
        <ContactInfoCard
          cpf={contact.cpf}
          email={contact.email}
          phone={contact.phone}
          createdAt={contact.created_at || null}
          updatedAt={contact.updated_at || null}
        />
        <ContactAddressCard address={contact.address || null} />
      </div>
      <div>
        <ContactMapCard name={contact.name} address={contact.address || null} />
      </div>
    </div>
  )
}

