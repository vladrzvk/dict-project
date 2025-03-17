# Utiliser une image Node avec la version correspondant à votre projet
FROM node:22 AS build

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de configuration du projet
COPY package*.json ./
COPY angular.json ./
COPY tsconfig*.json ./

# Installer Angular CLI globalement
RUN npm install -g @angular/cli@^19.2.3

# Installer les dépendances
RUN npm ci

# Copier tous les fichiers du projet
COPY . .

# Construire le projet
RUN ng build --configuration=production

# Image Nginx pour servir l'application
FROM nginx:alpine

# Copier la configuration Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copier les fichiers buildés depuis l'étape de build
COPY --from=build /app/dist/front /usr/share/nginx/html

# Exposer le port 80
EXPOSE 80

# Commande pour démarrer Nginx
CMD ["nginx", "-g", "daemon off;"]