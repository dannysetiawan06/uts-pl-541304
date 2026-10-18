# ==============================================================================
# Dockerfile: Vue.js 3 SPA Multi-Stage Build (Production Ready)
# Stage 1: Node.js Builder
# Stage 2: Nginx Lightweight Web Server (node_modules completely excluded)
# ==============================================================================

# Stage 1: Build SPA assets
FROM node:20-alpine AS builder

WORKDIR /app

# Layer Caching: Copy package definitions first
COPY package.json package-lock.json* ./

# Install Node dependencies
RUN npm install

# Copy frontend source code
COPY . .

# Build production bundle to /app/dist
RUN npm run build

# Stage 2: Production Web Server
FROM nginx:alpine AS runner

# Clean default Nginx html content
RUN rm -rf /usr/share/nginx/html/*

# Copy compiled production artifacts only (NO node_modules!)
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy custom SPA Nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]