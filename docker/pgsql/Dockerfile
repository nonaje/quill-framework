FROM postgres:14-alpine

ONBUILD RUN psql -U sigma-agro-api -d sigma-agro-api -c "create extension unaccent;"
