FROM kong:3.4

# Copy Kong configuration
COPY kong.yml /kong/kong.yml

# Set environment variables
ENV KONG_DATABASE=off
ENV KONG_DECLARATIVE_CONFIG=/kong/kong.yml
ENV KONG_PROXY_ACCESS_LOG=/dev/stdout
ENV KONG_ADMIN_ACCESS_LOG=/dev/stdout
ENV KONG_PROXY_ERROR_LOG=/dev/stderr
ENV KONG_ADMIN_ERROR_LOG=/dev/stderr

# Expose Kong ports
EXPOSE 8000 8001 8443 8444

# Start Kong
CMD ["kong", "docker-start"]

