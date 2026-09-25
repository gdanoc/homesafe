/**
 * Simulador de Entrega - HomeSafe
 * Simula el recorrido del repartidor desde su ubicación hasta el destino
 */

class DeliverySimulator {
    constructor(map, startCoords, endCoords, uiElements) {
        this.map = map;
        this.startCoords = startCoords;
        this.endCoords = endCoords;
        this.uiElements = uiElements;

        // Estado del simulador
        this.deliveryMarker = null;
        this.routeControl = null;
        this.currentPosition = { ...startCoords };
        this.isRunning = false;
        this.progress = 0;
        this.totalDistance = 0;
        this.estimatedTime = 0;
        this.startTime = null;
        this.animationInterval = null;
        this.routeCoordinates = [];

        // Configuración de la simulación
        this.config = {
            animationSpeed: 1000, // milisegundos entre actualizaciones
            progressIncrement: 0.033, // 3.3% por iteración (30 segundos total)
            mapPadding: [50, 50]
        };

        this.init();
    }

    /**
     * Inicializar el simulador
     */
    init() {
        console.log('Inicializando simulador de entrega...');
        console.log('Desde:', this.startCoords);
        console.log('Hasta:', this.endCoords);

        // Crear marcadores
        this.createMarkers();

        // Crear ruta
        this.createRoute();

        // Configurar eventos
        this.setupEventListeners();
    }

    /**
     * Crear marcadores en el mapa
     */
    createMarkers() {
        // Marcador de origen (repartidor inicial)
        const startMarker = L.marker([this.startCoords.lat, this.startCoords.lng], {
            icon: L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            })
        }).addTo(this.map);

        startMarker.bindPopup('📍 Ubicación inicial del repartidor');

        // Marcador de destino
        const endMarker = L.marker([this.endCoords.lat, this.endCoords.lng], {
            icon: L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            })
        }).addTo(this.map);

        endMarker.bindPopup('🏠 Destino de entrega');

        // Marcador del repartidor (móvil) - inicialmente en la posición de inicio
        this.deliveryMarker = L.marker([this.startCoords.lat, this.startCoords.lng], {
            icon: L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/2776/2776067.png',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            }),
            zIndexOffset: 1000 // Asegurar que esté encima de otros marcadores
        }).addTo(this.map);

        this.deliveryMarker.bindPopup('🚚 Repartidor en movimiento');
    }

    /**
     * Crear la ruta entre origen y destino
     */
    createRoute() {
        console.log('Creando ruta...');

        this.routeControl = L.Routing.control({
            waypoints: [
                L.latLng(this.startCoords.lat, this.startCoords.lng),
                L.latLng(this.endCoords.lat, this.endCoords.lng)
            ],
            routeWhileDragging: false,
            lineOptions: {
                styles: [{
                    color: '#007bff',
                    opacity: 0.8,
                    weight: 5
                }]
            },
            createMarker: function() {
                return null; // No crear marcadores automáticos de la ruta
            },
            addWaypoints: false,
            show: false, // No mostrar las instrucciones de navegación
            draggableWaypoints: false,
            fitSelectedRoutes: false
        }).on('routesfound', (e) => {
            console.log('Ruta encontrada:', e);
            this.handleRouteFound(e);
        }).on('routingerror', (e) => {
            console.error('Error al crear ruta:', e);
            this.handleRouteError(e);
        }).addTo(this.map);
    }

    /**
     * Manejar cuando se encuentra una ruta
     */
    handleRouteFound(e) {
        const route = e.routes[0];
        this.totalDistance = route.summary.totalDistance;
        this.estimatedTime = route.summary.totalTime;
        this.routeCoordinates = route.coordinates || [];

        console.log('Distancia total:', this.totalDistance, 'metros');
        console.log('Tiempo estimado:', this.estimatedTime, 'segundos');

        // Actualizar ETA en la UI
        this.updateETA(this.estimatedTime);

        // Actualizar distancia en la UI
        this.updateDistance(this.totalDistance);

        // Ajustar vista del mapa para mostrar toda la ruta
        this.fitMapToRoute();

        // Mostrar mensaje de ruta lista
        this.updateMessage('📍 Ruta calculada. Presiona "Iniciar Entrega" para comenzar la simulación.');
    }

    /**
     * Manejar errores de ruta
     */
    handleRouteError(e) {
        console.error('Error en la ruta:', e);
        this.updateMessage('❌ Error al calcular la ruta. Verifica las coordenadas.');
    }

    /**
     * Ajustar el mapa para mostrar toda la ruta
     */
    fitMapToRoute() {
        const bounds = L.latLngBounds([
            [this.startCoords.lat, this.startCoords.lng],
            [this.endCoords.lat, this.endCoords.lng]
        ]);

        this.map.fitBounds(bounds, { 
            padding: this.config.mapPadding,
            maxZoom: 15
        });
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Listener para cuando el mapa esté listo
        this.map.whenReady(() => {
            console.log('Mapa listo para simulación');
        });
    }

    /**
     * Iniciar la simulación
     */
    start() {
        if (this.isRunning) {
            console.log('La simulación ya está en ejecución');
            return;
        }

        console.log('Iniciando simulación de entrega...');

        this.isRunning = true;
        this.startTime = Date.now();
        this.progress = 0;

        // Resetear posición del marcador
        this.currentPosition = { ...this.startCoords };
        this.deliveryMarker.setLatLng([this.currentPosition.lat, this.currentPosition.lng]);

        // Actualizar UI
        this.updateMessage('🚚 Simulación iniciada - El repartidor está en camino...');
        this.updateStatus('En camino');

        // Iniciar animación
        this.animate();
    }

    /**
     * Animar el movimiento del repartidor
     */
    animate() {
        if (!this.isRunning) return;

        // Incrementar progreso
        this.progress += this.config.progressIncrement;

        if (this.progress >= 1) {
            this.progress = 1;
            this.complete();
            return;
        }

        // Calcular nueva posición (interpolación lineal)
        const newPosition = this.interpolatePosition(this.progress);
        this.currentPosition = newPosition;

        // Actualizar marcador
        this.deliveryMarker.setLatLng([newPosition.lat, newPosition.lng]);

        // Actualizar UI
        this.updateProgress(this.progress);

        // Continuar animación
        this.animationInterval = setTimeout(() => this.animate(), this.config.animationSpeed);
    }

    /**
     * Interpolar posición basada en el progreso
     */
    interpolatePosition(progress) {
        // Si tenemos coordenadas de ruta detalladas, usarlas
        if (this.routeCoordinates && this.routeCoordinates.length > 0) {
            const totalPoints = this.routeCoordinates.length;
            const targetIndex = Math.floor(progress * (totalPoints - 1));
            const coord = this.routeCoordinates[targetIndex];
            return { lat: coord.lat, lng: coord.lng };
        }

        // Interpolación lineal simple entre inicio y fin
        const lat = this.startCoords.lat + (this.endCoords.lat - this.startCoords.lat) * progress;
        const lng = this.startCoords.lng + (this.endCoords.lng - this.startCoords.lng) * progress;

        return { lat, lng };
    }

    /**
     * Completar la simulación
     */
    complete() {
        console.log('Simulación completada');

        this.isRunning = false;

        if (this.animationInterval) {
            clearTimeout(this.animationInterval);
            this.animationInterval = null;
        }

        // Posicionar marcador en destino final
        this.deliveryMarker.setLatLng([this.endCoords.lat, this.endCoords.lng]);

        // Actualizar UI
        this.updateMessage('✅ ¡Simulación completada! El repartidor ha llegado al destino.');
        this.updateStatus('Llegada');
        this.updateETA(0);
        this.updateProgress(1);

        // Centrar mapa en destino
        this.map.setView([this.endCoords.lat, this.endCoords.lng], 16);

        // Mostrar popup de llegada
        this.deliveryMarker.bindPopup('🎉 ¡Llegada al destino! Puedes finalizar la entrega.').openPopup();

        // Disparar evento personalizado de completado
        this.onComplete();
    }

    /**
     * Detener la simulación
     */
    stop() {
        console.log('Deteniendo simulación...');

        this.isRunning = false;

        if (this.animationInterval) {
            clearTimeout(this.animationInterval);
            this.animationInterval = null;
        }

        this.updateMessage('⏹️ Simulación detenida.');
        this.updateStatus('Detenida');
    }

    /**
     * Pausar la simulación
     */
    pause() {
        if (!this.isRunning) return;

        console.log('Pausando simulación...');

        this.isRunning = false;

        if (this.animationInterval) {
            clearTimeout(this.animationInterval);
            this.animationInterval = null;
        }

        this.updateMessage('⏸️ Simulación pausada.');
        this.updateStatus('Pausada');
    }

    /**
     * Reanudar la simulación
     */
    resume() {
        if (this.isRunning || this.progress >= 1) return;

        console.log('Reanudando simulación...');

        this.isRunning = true;
        this.updateMessage('▶️ Simulación reanudada.');
        this.updateStatus('En camino');

        this.animate();
    }

    /**
     * Actualizar mensaje en la UI
     */
    updateMessage(message) {
        if (this.uiElements.messageElement) {
            this.uiElements.messageElement.textContent = message;
        }
        console.log('Mensaje:', message);
    }

    /**
     * Actualizar estado en la UI
     */
    updateStatus(status) {
        if (this.uiElements.statusElement) {
            this.uiElements.statusElement.textContent = status;
        }
    }

    /**
     * Actualizar progreso en la UI
     */
    updateProgress(progress) {
        const percentage = Math.round(progress * 100);

        if (this.uiElements.progressElement) {
            this.uiElements.progressElement.style.width = percentage + '%';
        }

        if (this.uiElements.progressText) {
            this.uiElements.progressText.textContent = percentage + '%';
        }

        // Actualizar mensaje con progreso
        if (progress < 1) {
            this.updateMessage(`🚚 En camino... ${percentage}% completado`);
        }

        // Actualizar ETA basado en progreso
        if (this.estimatedTime > 0 && progress < 1) {
            const remainingTime = this.estimatedTime * (1 - progress);
            this.updateETA(remainingTime);
        }
    }

    /**
     * Actualizar ETA en la UI
     */
    updateETA(seconds) {
        if (!this.uiElements.etaElement) return;

        if (seconds <= 0) {
            this.uiElements.etaElement.textContent = '¡Llegada!';
            return;
        }

        const minutes = Math.ceil(seconds / 60);
        this.uiElements.etaElement.textContent = `${minutes} min${minutes !== 1 ? 's' : ''}`;
    }

    /**
     * Actualizar distancia en la UI
     */
    updateDistance(meters) {
        if (!this.uiElements.distanceElement) return;

        if (meters >= 1000) {
            const km = (meters / 1000).toFixed(1);
            this.uiElements.distanceElement.textContent = `${km} km`;
        } else {
            this.uiElements.distanceElement.textContent = `${Math.round(meters)} m`;
        }
    }

    /**
     * Callback cuando se completa la simulación
     */
    onComplete() {
        // Disparar evento personalizado
        if (typeof window !== 'undefined') {
            const event = new CustomEvent('deliverySimulationComplete', {
                detail: {
                    startCoords: this.startCoords,
                    endCoords: this.endCoords,
                    totalDistance: this.totalDistance,
                    estimatedTime: this.estimatedTime,
                    actualTime: Date.now() - this.startTime
                }
            });
            window.dispatchEvent(event);
        }

        // Callback personalizado si existe
        if (typeof this.onCompleteCallback === 'function') {
            this.onCompleteCallback();
        }
    }

    /**
     * Establecer callback de completado
     */
    setOnCompleteCallback(callback) {
        this.onCompleteCallback = callback;
    }

    /**
     * Obtener estado actual del simulador
     */
    getStatus() {
        return {
            isRunning: this.isRunning,
            progress: this.progress,
            currentPosition: this.currentPosition,
            totalDistance: this.totalDistance,
            estimatedTime: this.estimatedTime,
            startTime: this.startTime
        };
    }

    /**
     * Limpiar recursos del simulador
     */
    destroy() {
        console.log('Destruyendo simulador...');

        this.stop();

        // Remover marcadores
        if (this.deliveryMarker) {
            this.map.removeLayer(this.deliveryMarker);
        }

        // Remover control de ruta
        if (this.routeControl) {
            this.map.removeControl(this.routeControl);
        }

        // Limpiar referencias
        this.map = null;
        this.uiElements = null;
        this.deliveryMarker = null;
        this.routeControl = null;
    }
}

// Función auxiliar para crear un simulador
function createDeliverySimulator(map, startCoords, endCoords, uiElements = {}) {
    return new DeliverySimulator(map, startCoords, endCoords, uiElements);
}

// Exportar para uso en módulos (si es necesario)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DeliverySimulator, createDeliverySimulator };
}