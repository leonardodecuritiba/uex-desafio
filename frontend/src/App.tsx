import { useEffect, type ReactElement } from 'react'
import { api } from './shared/api'
import { Link, Route, BrowserRouter, Routes } from 'react-router-dom'
import RegisterPage from './features/auth/pages/RegisterPage'
import ForgotPasswordPage from './features/auth/pages/ForgotPassword'
import ResetPasswordPage from './features/auth/pages/ResetPassword'
import LoginPage from './features/auth/pages/LoginPage'
import ProfilePage from './features/auth/pages/ProfilePage'
import CreateContactPage from './features/contacts/pages/CreateContactPage'
import SearchContactsPage from './features/search/pages/SearchContactsPage'
import EditContactPage from './features/contacts/pages/EditContactPage'
import ContactDetailPage from './features/contacts/pages/ContactDetailPage'
import DeleteAccountPage from './features/account/pages/DeleteAccountPage'
import { AuthProvider, useAuth } from './shared/auth/AuthContext'
import ProtectedRoute from './shared/auth/ProtectedRoute'
import GuestRoute from './shared/auth/GuestRoute'

function App() {
  useEffect(() => {
    api.get('/api/health').catch(() => {})
  }, [])

  function Navbar(): ReactElement {
    const { isAuthenticated } = useAuth()
    return (
      <p style={{ display: 'flex', gap: 12 }}>
        {!isAuthenticated && <Link to="/login">Entrar</Link>}
        {!isAuthenticated && <Link to="/register">Criar conta</Link>}
        {!isAuthenticated && <Link to="/forgot-password">Esqueci minha senha</Link>}
        {isAuthenticated && <Link to="/me">Meu perfil</Link>}
        {isAuthenticated && <Link to="/contacts/new">Novo contato</Link>}
        {isAuthenticated && <Link to="/contacts">Buscar contatos</Link>}
      </p>
    )
  }

  return (
    <BrowserRouter>
      <AuthProvider>
        <div style={{ padding: 24 }}>
          <h1>UEX Contacts Monolith</h1>
          <Navbar />
          <Routes>
            <Route
              path="/login"
              element={
                <GuestRoute>
                  <LoginPage />
                </GuestRoute>
              }
            />
            <Route
              path="/register"
              element={
                <GuestRoute>
                  <RegisterPage />
                </GuestRoute>
              }
            />
            <Route
              path="/forgot-password"
              element={
                <GuestRoute>
                  <ForgotPasswordPage />
                </GuestRoute>
              }
            />
            <Route path="/reset-password" element={<ResetPasswordPage />} />
            <Route
              path="/me"
              element={
                <ProtectedRoute>
                  <ProfilePage />
                </ProtectedRoute>
              }
            />
            <Route
              path="/contacts"
              element={
                <ProtectedRoute>
                  <SearchContactsPage />
                </ProtectedRoute>
              }
            />
            <Route
              path="/contacts/new"
              element={
                <ProtectedRoute>
                  <CreateContactPage />
                </ProtectedRoute>
              }
            />
            <Route
              path="/contacts/:id"
              element={
                <ProtectedRoute>
                  <ContactDetailPage />
                </ProtectedRoute>
              }
            />
            <Route
              path="/contacts/:id/edit"
              element={
                <ProtectedRoute>
                  <EditContactPage />
                </ProtectedRoute>
              }
            />
            <Route
              path="/account/delete"
              element={
                <ProtectedRoute>
                  <DeleteAccountPage />
                </ProtectedRoute>
              }
            />
          </Routes>
        </div>
      </AuthProvider>
    </BrowserRouter>
  )
}

export default App
