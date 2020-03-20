FROM mysql:5.6

COPY . .
RUN zcat ./db_user.sql.gz > /docker-entrypoint-initdb.d/db_user.sql

EXPOSE 3306