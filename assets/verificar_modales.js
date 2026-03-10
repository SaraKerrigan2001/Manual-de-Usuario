// Script de verificación de modales
console.log('=== VERIFICACIÓN DE MODALES ===');

// Verificar que las funciones existen
console.log('Funciones disponibles:');
console.log('- abrirModalVerPerfilInst:', typeof abrirModalVerPerfilInst);
console.log('- abrirModalConfiguracionInst:', typeof abrirModalConfiguracionInst);
console.log('- abrirModalEditarPerfil:', typeof abrirModalEditarPerfil);

// Verificar que los modales existen en el DOM
console.log('\nModales en el DOM:');
console.log('- modal-ver-perfil-inst:', !!document.getElementById('modal-ver-perfil-inst'));
console.log('- modal-configuracion-inst:', !!document.getElementById('modal-configuracion-inst'));
console.log('- modal-editar-perfil:', !!document.getElementById('modal-editar-perfil'));

// Verificar CSS
const modal = document.getElementById('modal-ver-perfil-inst');
if (modal) {
    const styles = window.getComputedStyle(modal);
    console.log('\nEstilos del modal:');
    console.log('- display:', styles.display);
    console.log('- position:', styles.position);
    console.log('- z-index:', styles.zIndex);
}

console.log('\n=== FIN VERIFICACIÓN ===');
