/**
 * Sistema de Contexto de Rol para Múltiples Pestañas
 * 
 * Este script permite que cada pestaña mantenga su propio contexto de rol
 * independiente, evitando conflictos cuando se trabaja con Coordinador e
 * Instructor en pestañas separadas del mismo navegador.
 */

console.log('🔐 role_context.js cargado correctamente');

class RoleContextManager {
    constructor(rol) {
        this.rol = rol; // 'coordinador' o 'instructor'
        this.tabId = this.getOrCreateTabId();
        this.storageKey = `sena_role_context_${this.tabId}`;
        
        // Inicializar contexto
        this.initContext();
        
        // Limpiar contextos antiguos al cargar
        this.cleanOldContexts();
        
        // Registrar limpieza al cerrar pestaña
        window.addEventListener('beforeunload', () => this.cleanup());
    }
    
    /**
     * Obtener o crear ID único para esta pestaña
     */
    getOrCreateTabId() {
        // Usar sessionStorage para ID único por pestaña
        let tabId = sessionStorage.getItem('sena_tab_id');
        
        if (!tabId) {
            tabId = this.generateUniqueId();
            sessionStorage.setItem('sena_tab_id', tabId);
        }
        
        return tabId;
    }
    
    /**
     * Generar ID único
     */
    generateUniqueId() {
        return 'tab_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Inicializar contexto de la pestaña
     */
    initContext() {
        const context = {
            rol: this.rol,
            tabId: this.tabId,
            timestamp: Date.now(),
            active: true
        };
        
        sessionStorage.setItem(this.storageKey, JSON.stringify(context));
        
        // Marcar esta pestaña como activa en localStorage
        this.markTabActive();
    }
    
    /**
     * Marcar pestaña como activa
     */
    markTabActive() {
        const activeTabs = this.getActiveTabs();
        activeTabs[this.tabId] = {
            rol: this.rol,
            timestamp: Date.now()
        };
        localStorage.setItem('sena_active_tabs', JSON.stringify(activeTabs));
    }
    
    /**
     * Obtener pestañas activas
     */
    getActiveTabs() {
        const stored = localStorage.getItem('sena_active_tabs');
        return stored ? JSON.parse(stored) : {};
    }
    
    /**
     * Limpiar contextos antiguos (más de 1 hora)
     */
    cleanOldContexts() {
        const activeTabs = this.getActiveTabs();
        const now = Date.now();
        const oneHour = 60 * 60 * 1000;
        
        let cleaned = false;
        for (const [tabId, data] of Object.entries(activeTabs)) {
            if (now - data.timestamp > oneHour) {
                delete activeTabs[tabId];
                cleaned = true;
            }
        }
        
        if (cleaned) {
            localStorage.setItem('sena_active_tabs', JSON.stringify(activeTabs));
        }
    }
    
    /**
     * Obtener contexto actual
     */
    getContext() {
        const stored = sessionStorage.getItem(this.storageKey);
        return stored ? JSON.parse(stored) : null;
    }
    
    /**
     * Actualizar contexto
     */
    updateContext(data) {
        const context = this.getContext() || {};
        Object.assign(context, data);
        context.timestamp = Date.now();
        sessionStorage.setItem(this.storageKey, JSON.stringify(context));
    }
    
    /**
     * Verificar si esta pestaña es del rol especificado
     */
    isRole(rol) {
        return this.rol === rol;
    }
    
    /**
     * Obtener rol de esta pestaña
     */
    getRole() {
        return this.rol;
    }
    
    /**
     * Limpiar al cerrar pestaña
     */
    cleanup() {
        // Remover de pestañas activas
        const activeTabs = this.getActiveTabs();
        delete activeTabs[this.tabId];
        localStorage.setItem('sena_active_tabs', JSON.stringify(activeTabs));
        
        // Limpiar sessionStorage
        sessionStorage.removeItem(this.storageKey);
    }
    
    /**
     * Verificar si hay conflicto de roles
     */
    hasRoleConflict() {
        const activeTabs = this.getActiveTabs();
        const roles = Object.values(activeTabs).map(tab => tab.rol);
        
        // Si hay más de un rol diferente activo
        const uniqueRoles = [...new Set(roles)];
        return uniqueRoles.length > 1;
    }
    
    /**
     * Obtener información de pestañas activas
     */
    getActiveTabsInfo() {
        const activeTabs = this.getActiveTabs();
        const info = {
            total: Object.keys(activeTabs).length,
            coordinador: 0,
            instructor: 0,
            tabs: []
        };
        
        for (const [tabId, data] of Object.entries(activeTabs)) {
            if (data.rol === 'coordinador') info.coordinador++;
            if (data.rol === 'instructor') info.instructor++;
            
            info.tabs.push({
                id: tabId,
                rol: data.rol,
                isCurrent: tabId === this.tabId,
                timestamp: data.timestamp
            });
        }
        
        return info;
    }
    
    /**
     * Mantener pestaña activa (llamar periódicamente)
     */
    keepAlive() {
        this.markTabActive();
    }
}

// Función helper para inicializar el contexto
function initRoleContext(rol) {
    if (!['coordinador', 'instructor'].includes(rol)) {
        console.error('Rol inválido:', rol);
        return null;
    }
    
    const manager = new RoleContextManager(rol);
    
    // Mantener activa cada 30 segundos
    setInterval(() => manager.keepAlive(), 30000);
    
    // Log de información (solo en desarrollo)
    if (window.location.hostname === 'localhost') {
        console.log('🔐 Contexto de Rol Inicializado:', {
            rol: manager.getRole(),
            tabId: manager.tabId,
            pestañasActivas: manager.getActiveTabsInfo()
        });
    }
    
    return manager;
}

// Exportar para uso global
window.RoleContextManager = RoleContextManager;
window.initRoleContext = initRoleContext;
