# Script pour récupérer les logs des pods dans le namespace blog-dict
# Nom du fichier: get-pod-logs.ps1

# Configuration
$Namespace = "blog-dict"
$LogsDirectory = ".\k8s-logs-$(Get-Date -Format 'yyyyMMdd-HHmmss')"

# Fonction pour le logging
function Write-Log {
    param([string]$Message, [string]$Color = "Cyan")
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] $Message" -ForegroundColor $Color
}

# Créer le répertoire pour les logs
function Create-LogsDirectory {
    if (-not (Test-Path $LogsDirectory)) {
        New-Item -ItemType Directory -Path $LogsDirectory | Out-Null
        Write-Log "Répertoire des logs créé: $LogsDirectory" "Green"
    }
}

# Obtenir tous les pods dans le namespace
function Get-AllPods {
    try {
        $podsJson = kubectl get pods -n $Namespace -o json | ConvertFrom-Json
        return $podsJson.items
    }
    catch {
        Write-Log "Erreur lors de la récupération des pods: $_" "Red"
        return @()
    }
}

# Récupérer les logs d'un pod et de ses conteneurs
function Get-PodLogs {
    param(
        [string]$PodName,
        [array]$Containers
    )

    Write-Log "Récupération des logs pour le pod: $PodName" "Yellow"
    
    # Créer un répertoire pour ce pod
    $podDir = "$LogsDirectory\$PodName"
    if (-not (Test-Path $podDir)) {
        New-Item -ItemType Directory -Path $podDir | Out-Null
    }

    # Pour chaque conteneur dans le pod
    foreach ($container in $Containers) {
        $containerName = $container.name
        $logFile = "$podDir\$containerName.log"
        
        Write-Log "  - Conteneur: $containerName" "Magenta"
        
        try {
            # Récupérer les logs du conteneur
            kubectl logs -n $Namespace $PodName -c $containerName > $logFile 2>&1
            
            # Si le fichier est vide ou contient une erreur, essayer de récupérer les logs précédents
            $logContent = Get-Content $logFile -Raw
            if ([string]::IsNullOrWhiteSpace($logContent) -or $logContent -like "*Error from server*") {
                Write-Log "    Aucun log actif trouvé, tentative avec --previous" "Yellow"
                kubectl logs -n $Namespace $PodName -c $containerName --previous > "$podDir\${containerName}_previous.log" 2>&1
            }
            
            # Récupérer aussi les informations sur l'état du conteneur
            kubectl describe pod -n $Namespace $PodName > "$podDir\${PodName}_describe.txt"
            
            Write-Log "    Logs sauvegardés dans: $logFile" "Green"
        }
        catch {
            Write-Log "    Erreur lors de la récupération des logs: $_" "Red"
        }
    }
}

# Récupérer les logs des événements Kubernetes
function Get-KubernetesEvents {
    Write-Log "Récupération des événements Kubernetes pour le namespace $Namespace" "Yellow"
    try {
        kubectl get events -n $Namespace > "$LogsDirectory\kubernetes_events.log"
        Write-Log "Événements sauvegardés dans: $LogsDirectory\kubernetes_events.log" "Green"
    }
    catch {
        Write-Log "Erreur lors de la récupération des événements: $_" "Red"
    }
}

# Fonction principale
function Get-AllKubernetesLogs {
    Write-Log "Démarrage de la récupération des logs Kubernetes" "Cyan"
    
    # Créer le répertoire pour les logs
    Create-LogsDirectory
    
    # Récupérer les événements
    Get-KubernetesEvents
    
    # Récupérer tous les pods
    $pods = Get-AllPods
    
    if ($pods.Count -eq 0) {
        Write-Log "Aucun pod trouvé dans le namespace $Namespace" "Yellow"
        return
    }
    
    # Pour chaque pod, récupérer les logs
    foreach ($pod in $pods) {
        $podName = $pod.metadata.name
        $containers = $pod.spec.containers
        
        Get-PodLogs -PodName $podName -Containers $containers
    }
    
    # Récupérer les informations sur les déploiements, services, etc.
    Write-Log "Récupération des informations sur les ressources Kubernetes" "Yellow"
    
    $resources = @(
        "deployments", 
        "services", 
        "configmaps", 
        "pvc", 
        "ingress"
    )
    
    foreach ($resource in $resources) {
        try {
            kubectl get $resource -n $Namespace -o yaml > "$LogsDirectory\${resource}.yaml"
            Write-Log "Informations $resource sauvegardées" "Green"
        }
        catch {
            Write-Log "Erreur lors de la récupération des ${resource}: $_" "Red"
        }
    }
    
    Write-Log "Récupération des logs terminée. Logs disponibles dans: $LogsDirectory" "Green"
}

# Exécuter le script
Get-AllKubernetesLogs