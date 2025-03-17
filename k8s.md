NAMESPACE: blog-dict
|
├── CONFIGMAPS
|   ├── laravel-config      → Configuration Laravel (env variables)
|   ├── laravel-secrets     → Secrets Laravel (passwords, keys)
|   ├── nginx-config        → Configuration Nginx pour backend
|   ├── promtail-config     → Configuration Promtail pour logs
|   ├── loki-config         → Configuration Loki
|   ├── waf-config          → Configuration WAF pour frontend
|   ├── prometheus-config   → Configuration Prometheus
|   └── grafana-datasources → Sources de données Grafana
|
├── PERSISTENT VOLUME CLAIMS
|   ├── laravel-storage-pvc → Stockage Laravel
|   ├── mysql-data-pvc      → Stockage MySQL
|   ├── loki-storage-pvc    → Stockage Loki
|   ├── prometheus-storage-pvc → Stockage Prometheus
|   └── grafana-storage-pvc → Stockage Grafana
|
├── DEPLOYMENTS
|   ├── blog-backend        → Application Laravel + Nginx + Promtail
|   ├── blog-frontend       → Application Angular + WAF
|   ├── mysql               → Base de données
|   ├── loki                → Système de logs
|   ├── dict-prometheus     → Monitoring Prometheus
|   ├── dict-grafana        → Dashboard Grafana
|   └── phpmyadmin          → Interface MySQL
|
├── SERVICES
|   ├── blog-backend        → Expose backend (ClusterIP)
|   ├── blog-frontend       → Expose frontend (ClusterIP)
|   ├── mysql               → Expose MySQL (ClusterIP)
|   ├── loki                → Expose Loki (ClusterIP)
|   ├── dict-prometheus     → Expose Prometheus (ClusterIP)
|   ├── dict-grafana        → Expose Grafana (ClusterIP)
|   └── phpmyadmin          → Expose phpMyAdmin (ClusterIP)
|
└── INGRESS
    └── blog-dict-ingress   → Point d'entrée externe


# 1. Namespace
kubectl apply -f 00-namespace.yml

# 2. ConfigMaps et Secrets
kubectl apply -f 01-backend-config.yml
kubectl apply -f nginx-config.yml
kubectl apply -f promtail-config.yml
kubectl apply -f waf-config.yml

# 3. PersistentVolumeClaims
kubectl apply -f 09-storage.yml  # Si vous avez créé ce fichier pour les PVCs

# 4. Infrastructure de base
kubectl apply -f 10-mysql.yml

# 5. Infra de monitoring
kubectl apply -f loki-deployment.yml
kubectl apply -f 07-dict-monitoring.yml

# 6. Applications
kubectl apply -f 02-backend-deployment.yml
kubectl apply -f 03-backend-service.yml
kubectl apply -f 04-frontend-deployment.yml
kubectl apply -f 05-frontend-service.yml
kubectl apply -f 08-phpmyadmin.yml

# 7. Ingress (point d'entrée)
kubectl apply -f 06-ingress.yml