FROM node:22 as build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY . .
RUN npm run build -- --prod

FROM nginx:alpine

# Copier la configuration nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copier les fichiers compilés depuis l'étape de construction
COPY --from=build /app/dist/your-app-name /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]