CREATE SEQUENCE files_id_seq START 1;
CREATE TABLE files
(
    id integer NOT NULL DEFAULT nextval('files_id_seq'::regclass),
    name character varying(20) COLLATE pg_catalog."default" NOT NULL,
    data json,
    date timestamp(4) without time zone NOT NULL,
    comment text COLLATE pg_catalog."default",
    CONSTRAINT files_pkey PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;
