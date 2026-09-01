# Production Dockerfile for Baileys WhatsApp Bridge
FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install git and build essentials if required by native modules
RUN apk update && apk add --no-cache git tzdata

# Copy package files
COPY package*.json ./

# Install npm dependencies
RUN npm install --omit=dev

# Copy application files
COPY . .

# Ensure session storage folder exists
RUN mkdir -p /app/sessions

# Expose Bridge Port
EXPOSE 3001

# Start Baileys Bridge
CMD ["node", "index.js"]
