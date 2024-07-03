FROM nginx:alpine
WORKDIR /home/site/wwwroot
RUN chown nginx:nginx .
COPY --chown=nginx . .
RUN find . -type f -exec chmod 400 {} + -o -type d -exec chmod 100 {} +
