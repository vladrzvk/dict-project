<?php

namespace App\Http\Services\Dict;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DictConfidentialityService
{
    /**
     * Les en-têtes de sécurité à appliquer
     * 
     * @var array
     */
    protected $securityHeaders = [
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
    ];
    
    /**
     * Configuration CSP de base
     * 
     * @var array
     */
    protected $cspDirectives = [
        'default-src' => "'self'",
        'script-src' => "'self'",
        'style-src' => "'self'",
        'img-src' => "'self' data:",
        'font-src' => "'self'",
        'connect-src' => "'self'",
        'media-src' => "'self'",
        'object-src' => "'none'",
        'child-src' => "'self'",
        'frame-ancestors' => "'self'",
        'form-action' => "'self'",
        'base-uri' => "'self'"
    ];
    
    /**
     * Appliquer les en-têtes de sécurité à une réponse
     * 
     * @param Response $response
     * @return Response
     */
    public function applySecurityHeaders($response)
    {
        // Appliquer les en-têtes de sécurité standard
        foreach ($this->securityHeaders as $header => $value) {
            $response->headers->set($header, $value);
        }
        
        // Construire et appliquer l'en-tête Content-Security-Policy
        $cspHeader = $this->buildCspHeader();
        $response->headers->set('Content-Security-Policy', $cspHeader);
        
        return $response;
    }
    
    /**
     * Construire l'en-tête Content-Security-Policy
     * 
     * @return string
     */
    private function buildCspHeader()
    {
        $directives = [];
        
        foreach ($this->cspDirectives as $directive => $value) {
            $directives[] = $directive . ' ' . $value;
        }
        
        return implode('; ', $directives);
    }
    
    /**
     * Personnaliser la politique CSP pour l'environnement de développement
     * 
     * @return self
     */
    public function configureForDevelopment()
    {
        // En développement, on permet plus de choses pour faciliter le debug
        $this->cspDirectives['script-src'] = "'self' 'unsafe-inline' 'unsafe-eval'";
        $this->cspDirectives['style-src'] = "'self' 'unsafe-inline'";
        $this->cspDirectives['connect-src'] = "'self' localhost:*";
        
        return $this;
    }
    
    /**
     * Personnaliser la politique CSP pour l'environnement de production
     * 
     * @return self
     */
    public function configureForProduction()
    {
        // En production, on est plus restrictif
        $this->cspDirectives['script-src'] = "'self'";
        $this->cspDirectives['style-src'] = "'self'";
        
        return $this;
    }
    
    /**
     * Vérifier si une requête HTTP contient des données sensibles
     * 
     * @param Request $request
     * @return bool
     */
    public function containsSensitiveData(Request $request)
    {
        $sensitiveParams = ['password', 'secret', 'token', 'key', 'credit_card', 'ssn'];
        
        foreach ($sensitiveParams as $param) {
            if ($request->has($param)) {
                Log::warning('DICT:Confidentialité - Données sensibles détectées', [
                    'parameter' => $param,
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'path' => $request->path()
                ]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Obtenir un rapport sur la configuration de confidentialité
     * 
     * @return array
     */
    public function getConfidentialityReport()
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'headers_applied' => array_merge($this->securityHeaders, ['Content-Security-Policy' => $this->buildCspHeader()]),
            'environment' => app()->environment(),
            'tls_enabled' => request()->secure()
        ];
    }
}