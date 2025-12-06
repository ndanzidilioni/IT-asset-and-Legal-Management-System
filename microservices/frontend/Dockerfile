FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package files from main frontend directory
COPY frontend/package*.json ./

# Install dependencies
RUN npm install

# Copy application code from main frontend directory
COPY frontend/ ./

# Expose port
EXPOSE 3000

# Start the application
CMD ["npm", "start"]
