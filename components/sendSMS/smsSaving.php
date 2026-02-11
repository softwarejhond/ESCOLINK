<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnSendMassSMS').addEventListener('click', function() {
        // Primero, contar posibles envíos
        fetch('components/sendSMS/send_mass_sms_savings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=count'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const count = data.count;
                Swal.fire({
                    title: 'Confirmar Envío de SMS de ahorros',
                    text: `Se enviarán ${count} mensajes. ¿Continuar?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Enviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loader
                        Swal.fire({
                            title: 'Enviando SMS...',
                            text: 'Por favor espera.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Enviar
                        fetch('components/sendSMS/send_mass_sms_savings.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=send'
                        })
                        .then(response => response.json())
                        .then(sendData => {
                            Swal.close();
                            if (sendData.success) {
                                const sent = sendData.sent;
                                const errors = sendData.errors;
                                let message = `Se enviaron ${sent} mensajes exitosamente.`;
                                if (errors.length > 0) {
                                    message += `\nErrores:\n${errors.join('\n')}`;
                                }
                                Swal.fire({
                                    title: 'Envío Completado',
                                    text: message,
                                    icon: 'info'
                                });
                            } else {
                                Swal.fire('Error', sendData.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire('Error', 'Hubo un problema al enviar.', 'error');
                        });
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Hubo un problema al contar.', 'error');
        });
    });
});
</script>