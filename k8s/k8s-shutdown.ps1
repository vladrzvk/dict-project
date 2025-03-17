# Kubernetes Cluster Shutdown Script
# Usage: .\k8s-shutdown.ps1

# Configuration
$Namespace = "blog-dict"
$Deployments = @(
    "blog-backend", 
    "blog-frontend", 
    "mysql", 
    "dict-prometheus", 
    "dict-grafana", 
    "loki", 
    "phpmyadmin"
)

# Function for logging
function Write-Log {
    param([string]$Message, [string]$Color = "Cyan")
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] $Message" -ForegroundColor $Color
}

# Validate Kubernetes connection
function Test-KubernetesConnection {
    try {
        $context = kubectl config current-context
        Write-Log "Connected to Kubernetes context: $context"
    }
    catch {
        Write-Log "Unable to connect to Kubernetes cluster. Exiting." "Red"
        exit 1
    }
}

# Scale down deployments
function Stop-KubernetesDeployments {
    Write-Log "Scaling down deployments to 0 replicas..."
    foreach ($deployment in $Deployments) {
        try {
            # Vérifier si le déploiement existe
            $deploymentExists = kubectl get deployment $deployment -n $Namespace 2>&1
            
            if ($deploymentExists -like "*not found*") {
                Write-Log "Deployment $deployment not found, skipping..." "Yellow"
                continue
            }
            
            # Si le déploiement existe, le mettre à l'échelle
            kubectl scale deployment $deployment --replicas=0 -n $Namespace
            Write-Log "Scaled $deployment to 0 replicas" "Green"
        }
        catch {
            Write-Log "Warning: Could not scale $deployment" "Yellow"
        }
    }
}

# Graceful resource deletion
function Remove-KubernetesResources {
    $resourceTypes = @(
        "ingress",
        "service", 
        "deployment", 
        "pvc", 
        "configmap", 
        "secret"
    )

    foreach ($resourceType in $resourceTypes) {
        Write-Log "Deleting $resourceType resources..." "Cyan"
        try {
            # Vérifier s'il y a des ressources à supprimer
            $resourceCount = (kubectl get $resourceType -n $Namespace 2>&1 | Measure-Object -Line).Lines
            
            if ($resourceCount -le 1) {
                Write-Log "No $resourceType resources found to delete." "Yellow"
                continue
            }
            
            kubectl delete $resourceType --all -n $Namespace
            Write-Log "Deleted all $resourceType in namespace $Namespace" "Green"
        }
        catch {
            Write-Log "Warning: Could not delete $resourceType" "Yellow"
        }
    }
}

# Optional: Delete Namespace
function Remove-Namespace {
    $confirmation = Read-Host "Do you want to delete the entire namespace '$Namespace'? (yes/no)"
    if ($confirmation -eq 'yes') {
        try {
            # Vérifier si le namespace existe
            $namespaceExists = kubectl get namespace $Namespace 2>&1
            
            if ($namespaceExists -like "*not found*") {
                Write-Log "Namespace $Namespace does not exist." "Yellow"
                return
            }
            
            kubectl delete namespace $Namespace
            Write-Log "Namespace $Namespace deleted successfully" "Green"
        }
        catch {
            Write-Log "Failed to delete namespace $Namespace" "Red"
        }
    }
}

# Main execution
function Invoke-ClusterShutdown {
    Write-Log "Starting Kubernetes Cluster Shutdown"
    Test-KubernetesConnection
    Stop-KubernetesDeployments
    Start-Sleep -Seconds 5  # Petit délai pour permettre la descente en échelle
    Remove-KubernetesResources
    Remove-Namespace
    Write-Log "Cluster shutdown process completed" "Green"
}

# Run the shutdown process
Invoke-ClusterShutdown