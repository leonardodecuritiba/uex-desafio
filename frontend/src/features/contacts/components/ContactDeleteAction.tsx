import { useState } from 'react'
import { Button, Dialog, DialogTitle, DialogContent, DialogContentText, DialogActions } from '@mui/material'
import { useDeleteContact } from '../hooks/useDeleteContact'

type Props = {
  id: number
  variant?: 'list' | 'detail'
  onDeleted?: () => void
}

export default function ContactDeleteAction({ id, variant = 'detail', onDeleted }: Props) {
  const [open, setOpen] = useState(false)
  const mutation = useDeleteContact()

  function handleOpen() { setOpen(true) }
  function handleClose() { if (!mutation.isPending) setOpen(false) }

  async function confirmDelete() {
    try {
      await mutation.mutateAsync({ id })
      setOpen(false)
      if (variant === 'detail' && onDeleted) onDeleted()
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (err) {
      // Mensagens específicas podem ser tratadas pelo chamador; aqui apenas fecha o loading
    }
  }

  return (
    <>
      <Button color="error" variant="outlined" onClick={handleOpen} disabled={mutation.isPending}>
        Excluir
      </Button>
      <Dialog open={open} onClose={handleClose} aria-labelledby="delete-title" aria-describedby="delete-desc">
        <DialogTitle id="delete-title">Excluir contato</DialogTitle>
        <DialogContent>
          <DialogContentText id="delete-desc">
            Esta ação é irreversível. Deseja continuar?
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} autoFocus disabled={mutation.isPending}>Cancelar</Button>
          <Button onClick={confirmDelete} color="error" disabled={mutation.isPending}>
            {mutation.isPending ? 'Excluindo...' : 'Excluir'}
          </Button>
        </DialogActions>
      </Dialog>
    </>
  )
}

