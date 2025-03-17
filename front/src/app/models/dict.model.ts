// Créez ce fichier si nécessaire
export interface HealthComponent {
    status: string;
    [key: string]: any;
  }
  
  export interface HealthStatus {
    status: string;
    timestamp: string;
    components?: {
      [key: string]: HealthComponent;
    };
  }
  
  export interface IntegrityStatus {
    status: string;
    timestamp: string;
    checks_performed: boolean;
    issues: Array<{
      type: string;
      message: string;
      [key: string]: any;
    }>;
  }
  
  export interface ConfidentialityStatus {
    timestamp: string;
    headers_applied: {
      [key: string]: string;
    };
    environment: string;
    tls_enabled: boolean;
  }
  
  export interface ActivityStatus {
    timestamp: string;
    period: string;
    request_count: number;
    error_count: number;
    average_response_time_ms: number;
  }
  
  export interface StatsStatus {
    request_count: number;
    average_response_time: number;
    error_count: number;
    system_health: string;
  }
  
  export interface DictDashboard {
    health: HealthStatus;
    integrity: IntegrityStatus;
    confidentiality: ConfidentialityStatus;
    activity: ActivityStatus;
    stats: StatsStatus;
  }