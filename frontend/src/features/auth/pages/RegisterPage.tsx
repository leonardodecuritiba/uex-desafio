import { Container, Typography, Box, Paper } from '@mui/material'
import { RegisterForm } from '../components/RegisterForm'

export default function RegisterPage() {
  return (
    <Container maxWidth="sm" sx={{ py: 6 }}>
      <Paper elevation={3} sx={{ p: 3 }}>
        <Box display="flex" flexDirection="column" gap={2}>
          <Typography variant="h4" component="h1">
            Criar conta
          </Typography>
          <RegisterForm />
        </Box>
      </Paper>
    </Container>
  )
}

