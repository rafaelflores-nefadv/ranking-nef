/**
 * Exportações principais do core
 */

// Types
export * from './types';

// Store
export { AppStoreProvider, useAppStore } from './store/AppStore';

// Hooks
export { useRanking } from './hooks/useRanking';
export { useRealtime } from './hooks/useRealtime';
export { useInitialData } from './hooks/useInitialData';

// Services
export { createApiClient, getApiClient } from './services/api/client';
export { scoresService } from './services/api/scores';
export { sellersService } from './services/api/sellers';
export { teamsService } from './services/api/teams';
export { seasonsService } from './services/api/seasons';
export { configService } from './services/api/config';

// Constants
export { hasPermission, hasAnyPermission, hasAllPermissions, ROLE_PERMISSIONS } from './constants/permissions';

