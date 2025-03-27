# Encoding: UTF-8
$OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Kubernetes Cluster Startup Script
# Gère la construction des images Docker et le déploiement Kubernetes

# Configuration
$Namespace = "blog-dict"
$ProjectRoot = Resolve-Path ".."
$BackendContext = "$ProjectRoot\back"
$FrontendContext = "$ProjectRoot\front"
$BackendImageName = "blog-dict-backend"
$FrontendImageName = "blog-dict-frontend"
$BackendDockerfile = "$BackendContext\Dockerfile"
$FrontendDockerfile = "$FrontendContext\Dockerfile"

# Logging Function
function Write-Log {
    param([string]$Message, [string]$Color = "Cyan")
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] $Message" -ForegroundColor $Color
}

# Validate Prerequisites
function Test-Prerequisites {
    # Vérifier Docker
    try {
        $dockerVersion = docker version --format '{{.Server.Version}}'
        Write-Log "Docker version: $dockerVersion" "Green"
    }
    catch {
        Write-Log "Docker n'est pas installé ou n'est pas accessible" "Red"
        exit 1
    }

    # Vérifier Kubernetes
    try {
        $kubeContext = kubectl config current-context
        Write-Log "Kubernetes context: $kubeContext" "Green"
    }
    catch {
        Write-Log "Kubernetes n'est pas configuré" "Red"
        exit 1
    }
}

# Build Backend Docker Image
function Build-BackendImage {
    Write-Log "Construction de l'image backend..."
    
    # Vérifier l'existence du Dockerfile
    if (-not (Test-Path $BackendDockerfile)) {
        Write-Log "Dockerfile backend introuvable : $BackendDockerfile" "Red"
        return $false
    }

    # Construction de l'image
   try {
        Push-Location $BackendContext
        docker build --build-arg user=bloguser --build-arg uid=1000 -t "${BackendImageName}:latest" .
        Pop-Location
        Write-Log "Image backend construite avec succès" "Green"
        return $true
    }
    catch {
        Write-Log "Erreur lors de la construction de l'image backend" "Red"
        return $false
    }
}

# Build Frontend Docker Image
function Build-FrontendImage {
    Write-Log "Construction de l'image frontend..."
    
    # Vérifier l'existence du Dockerfile
    if (-not (Test-Path $FrontendDockerfile)) {
        Write-Log "Dockerfile frontend introuvable : $FrontendDockerfile" "Red"
        return $false
    }

    # Construction de l'image
    try {
        Push-Location $FrontendContext
        docker build -t "${FrontendImageName}:latest" .
        Pop-Location
        Write-Log "Image frontend construite avec succès" "Green"
        return $true
    }
    catch {
        Write-Log "Erreur lors de la construction de l'image frontend" "Red"
        return $false
    }
}

# Pull WAF Image
function Pull-WAFImage {
    param(
        [string]$ImageTag = "docker.io/owasp/modsecurity-crs:3.3-nginx"
    )
    Write-Log "Récupération de l'image WAF: $ImageTag" "Yellow"
    try {
        docker pull $ImageTag
        Write-Log "Image WAF récupérée avec succès" "Green"
        return $true
    }
    catch {
        Write-Log "Erreur lors de la récupération de l'image WAF" "Red"
        return $false
    }
}


# Créer le namespace
function Create-Namespace {
    Write-Log "Création du namespace Kubernetes..."
    try {
        kubectl apply -f 00-namespace.yml
        Write-Log "Namespace créé avec succès" "Green"
    }
    catch {
        Write-Log "Erreur lors de la création du namespace" "Red"
    }
}

# Déployer les ConfigMaps et Secrets
function Deploy-ConfigsAndSecrets {
    $configFiles = @(
        "01-backend-config.yml",
        "12-nginx-config.yml", 
        "14-promtail-config.yml", 
        "15-waf-config.yml",
        "16-waf-nginx-config.yml",
        "13-prometheus-config.yml"
    )

    foreach ($file in $configFiles) {
        try {
            kubectl apply -f $file -n $Namespace
            Write-Log "Déploiement de $file réussi" "Green"
        }
        catch {
            Write-Log "Erreur lors du déploiement de $file" "Red"
        }
    }
}

# Déployer les ressources Kubernetes
function Deploy-KubernetesResources {
    $deploymentFiles = @(
        "09-storage.yml",
        "10-mysql.yml",
        "11-loki-deployment.yml",
        "07-dict-monitoring.yml",
        "02-backend-deployment.yml",
        "03-backend-service.yml",
        "04-frontend-deployment.yml",
        "05-frontend-service.yml",
        "08-phpmyadmin.yml",
        "06-ingress.yml"
    )

    foreach ($file in $deploymentFiles) {
        try {
            kubectl apply -f $file -n $Namespace
            Write-Log "Déploiement de $file réussi" "Green"
        }
        catch {
            Write-Log "Erreur lors du déploiement de $file" "Red"
        }
    }
}

# Vérifier le statut des pods
function Check-PodStatus {
    Write-Log "Vérification du statut des pods..." "Yellow"
    try {
        kubectl get pods -n $Namespace
    }
    catch {
        Write-Log "Impossible de récupérer le statut des pods" "Red"
    }
}

# Main Startup Function
function Start-KubernetesCluster {
    Write-Log "Démarrage du cluster Kubernetes" "Magenta"
    
    # Vérifier les prérequis
    Test-Prerequisites

    # Construire les images Docker
    $backendBuild = Build-BackendImage
    $frontendBuild = Build-FrontendImage
    $wafPull = Pull-WAFImage

    # Vérifier les constructions d'images
    if (-not ($backendBuild -and $frontendBuild -and $wafPull)) {
        Write-Log "Échec de la préparation des images Docker" "Red"
        exit 1
    }

    # Déploiement Kubernetes
    Create-Namespace
    Deploy-ConfigsAndSecrets
    Deploy-KubernetesResources

    # Vérification finale
    Start-Sleep -Seconds 30
    Check-PodStatus

    Write-Log "Déploiement du cluster terminé" "Green"
}

# Exécuter le script
Start-KubernetesCluster