-- Base de datos TAMPU para PostgreSQL (Supabase)
-- Solo hace falta si se quiere cargar a mano desde el SQL Editor de Supabase.
-- Al desplegar en Render las tablas se crean solas con las migraciones.


-- Name: cache; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);

-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);

-- Name: contactos; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.contactos (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    asunto character varying(80) NOT NULL,
    mensaje text NOT NULL,
    leido boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

-- Name: contactos_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.contactos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: contactos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.contactos_id_seq OWNED BY public.contactos.id;

-- Name: conversaciones; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.conversaciones (
    id bigint NOT NULL,
    usuario_a_id bigint NOT NULL,
    usuario_b_id bigint NOT NULL,
    habitacion_id bigint,
    trabajo_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

-- Name: conversaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.conversaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: conversaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.conversaciones_id_seq OWNED BY public.conversaciones.id;

-- Name: eventos_seguridad; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.eventos_seguridad (
    id bigint NOT NULL,
    usuario_id bigint,
    email character varying(100),
    tipo character varying(30) NOT NULL,
    detalle character varying(255),
    ip character varying(45),
    navegador character varying(255),
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- Name: eventos_seguridad_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.eventos_seguridad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: eventos_seguridad_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.eventos_seguridad_id_seq OWNED BY public.eventos_seguridad.id;

-- Name: favoritos; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.favoritos (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    habitacion_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

-- Name: favoritos_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.favoritos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: favoritos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.favoritos_id_seq OWNED BY public.favoritos.id;

-- Name: habitacion_fotos; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.habitacion_fotos (
    id bigint NOT NULL,
    habitacion_id bigint NOT NULL,
    ruta character varying(255) NOT NULL,
    descripcion character varying(80),
    orden smallint DEFAULT '0'::smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

-- Name: habitacion_fotos_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.habitacion_fotos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: habitacion_fotos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.habitacion_fotos_id_seq OWNED BY public.habitacion_fotos.id;

-- Name: habitaciones; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.habitaciones (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    titulo character varying(120) NOT NULL,
    descripcion text NOT NULL,
    distrito character varying(40) NOT NULL,
    direccion character varying(150) NOT NULL,
    cerca_de character varying(80),
    precio numeric(7,2) NOT NULL,
    tipo character varying(255) NOT NULL,
    servicios character varying(200),
    foto character varying(255),
    estado character varying(255) DEFAULT 'disponible'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    lat numeric(10,7),
    lng numeric(10,7),
    metros smallint,
    piso smallint,
    bano character varying(12),
    garantia numeric(7,2),
    CONSTRAINT habitaciones_estado_check CHECK (((estado)::text = ANY ((ARRAY['disponible'::character varying, 'ocupada'::character varying])::text[]))),
    CONSTRAINT habitaciones_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['individual'::character varying, 'compartida'::character varying, 'minidepartamento'::character varying])::text[])))
);

-- Name: habitaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.habitaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: habitaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.habitaciones_id_seq OWNED BY public.habitaciones.id;

-- Name: horarios; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.horarios (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    dia character varying(255) NOT NULL,
    turno character varying(255) NOT NULL,
    CONSTRAINT horarios_dia_check CHECK (((dia)::text = ANY ((ARRAY['lunes'::character varying, 'martes'::character varying, 'miercoles'::character varying, 'jueves'::character varying, 'viernes'::character varying, 'sabado'::character varying, 'domingo'::character varying])::text[]))),
    CONSTRAINT horarios_turno_check CHECK (((turno)::text = ANY ((ARRAY['manana'::character varying, 'tarde'::character varying, 'noche'::character varying])::text[])))
);

-- Name: horarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.horarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: horarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.horarios_id_seq OWNED BY public.horarios.id;

-- Name: mensajes; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.mensajes (
    id bigint NOT NULL,
    conversacion_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    texto text NOT NULL,
    leido boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

-- Name: mensajes_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.mensajes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: mensajes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.mensajes_id_seq OWNED BY public.mensajes.id;

-- Name: migrations; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);

-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;

-- Name: notifications; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    type character varying(255) NOT NULL,
    notifiable_type character varying(255) NOT NULL,
    notifiable_id bigint NOT NULL,
    data text NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);

-- Name: postulaciones; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.postulaciones (
    id bigint NOT NULL,
    trabajo_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    mensaje character varying(300),
    estado character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT postulaciones_estado_check CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'aceptada'::character varying, 'rechazada'::character varying])::text[])))
);

-- Name: postulaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.postulaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: postulaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.postulaciones_id_seq OWNED BY public.postulaciones.id;

-- Name: reportes; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.reportes (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    habitacion_id bigint,
    trabajo_id bigint,
    motivo character varying(60) NOT NULL,
    detalle character varying(500),
    estado character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT reportes_estado_check CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'descartado'::character varying, 'sancionado'::character varying])::text[])))
);

-- Name: reportes_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.reportes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: reportes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.reportes_id_seq OWNED BY public.reportes.id;

-- Name: resenas; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.resenas (
    id bigint NOT NULL,
    habitacion_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    estrellas smallint NOT NULL,
    comentario character varying(500) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

-- Name: resenas_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.resenas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: resenas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.resenas_id_seq OWNED BY public.resenas.id;

-- Name: sessions; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);

-- Name: solicitudes; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.solicitudes (
    id bigint NOT NULL,
    habitacion_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    fecha_visita date NOT NULL,
    mensaje character varying(300),
    estado character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT solicitudes_estado_check CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'aceptada'::character varying, 'rechazada'::character varying])::text[])))
);

-- Name: solicitudes_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.solicitudes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: solicitudes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.solicitudes_id_seq OWNED BY public.solicitudes.id;

-- Name: trabajo_horarios; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.trabajo_horarios (
    id bigint NOT NULL,
    trabajo_id bigint NOT NULL,
    dia character varying(255) NOT NULL,
    turno character varying(255) NOT NULL,
    CONSTRAINT trabajo_horarios_dia_check CHECK (((dia)::text = ANY ((ARRAY['lunes'::character varying, 'martes'::character varying, 'miercoles'::character varying, 'jueves'::character varying, 'viernes'::character varying, 'sabado'::character varying, 'domingo'::character varying])::text[]))),
    CONSTRAINT trabajo_horarios_turno_check CHECK (((turno)::text = ANY ((ARRAY['manana'::character varying, 'tarde'::character varying, 'noche'::character varying])::text[])))
);

-- Name: trabajo_horarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.trabajo_horarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: trabajo_horarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.trabajo_horarios_id_seq OWNED BY public.trabajo_horarios.id;

-- Name: trabajos; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.trabajos (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    titulo character varying(120) NOT NULL,
    empresa character varying(100) NOT NULL,
    descripcion text NOT NULL,
    distrito character varying(40) NOT NULL,
    modalidad character varying(255) NOT NULL,
    pago numeric(7,2) NOT NULL,
    pago_por character varying(255) NOT NULL,
    estado character varying(255) DEFAULT 'activa'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    direccion character varying(150),
    lat numeric(10,7),
    lng numeric(10,7),
    requisitos text,
    vacantes smallint DEFAULT '1'::smallint NOT NULL,
    CONSTRAINT trabajos_estado_check CHECK (((estado)::text = ANY ((ARRAY['activa'::character varying, 'cerrada'::character varying])::text[]))),
    CONSTRAINT trabajos_modalidad_check CHECK (((modalidad)::text = ANY ((ARRAY['medio tiempo'::character varying, 'por horas'::character varying, 'practicas'::character varying])::text[]))),
    CONSTRAINT trabajos_pago_por_check CHECK (((pago_por)::text = ANY ((ARRAY['hora'::character varying, 'dia'::character varying, 'mes'::character varying])::text[])))
);

-- Name: trabajos_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.trabajos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: trabajos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.trabajos_id_seq OWNED BY public.trabajos.id;

-- Name: usuarios; Type: TABLE; Schema: public; Owner: -

CREATE TABLE public.usuarios (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    dni character(8) NOT NULL,
    celular character varying(9),
    procedencia character varying(60),
    rol character varying(255) DEFAULT 'estudiante'::character varying NOT NULL,
    verificacion character varying(255) DEFAULT 'no_aplica'::character varying NOT NULL,
    documento character varying(255),
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    foto character varying(255),
    suspendido boolean DEFAULT false NOT NULL,
    email_verificado_at timestamp(0) without time zone,
    codigo_correo character varying(255),
    codigo_correo_vence timestamp(0) without time zone,
    correo_institucional character varying(100),
    dos_pasos_activo boolean DEFAULT false NOT NULL,
    dos_pasos_secreto text,
    dos_pasos_codigos text,
    dos_pasos_ultimo bigint,
    CONSTRAINT usuarios_rol_check CHECK (((rol)::text = ANY ((ARRAY['estudiante'::character varying, 'arrendador'::character varying, 'empleador'::character varying, 'admin'::character varying])::text[]))),
    CONSTRAINT usuarios_verificacion_check CHECK (((verificacion)::text = ANY ((ARRAY['no_aplica'::character varying, 'pendiente'::character varying, 'verificado'::character varying, 'rechazado'::character varying])::text[])))
);

-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -

CREATE SEQUENCE public.usuarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;

-- Name: contactos id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.contactos ALTER COLUMN id SET DEFAULT nextval('public.contactos_id_seq'::regclass);

-- Name: conversaciones id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.conversaciones ALTER COLUMN id SET DEFAULT nextval('public.conversaciones_id_seq'::regclass);

-- Name: eventos_seguridad id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.eventos_seguridad ALTER COLUMN id SET DEFAULT nextval('public.eventos_seguridad_id_seq'::regclass);

-- Name: favoritos id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos ALTER COLUMN id SET DEFAULT nextval('public.favoritos_id_seq'::regclass);

-- Name: habitacion_fotos id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.habitacion_fotos ALTER COLUMN id SET DEFAULT nextval('public.habitacion_fotos_id_seq'::regclass);

-- Name: habitaciones id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.habitaciones ALTER COLUMN id SET DEFAULT nextval('public.habitaciones_id_seq'::regclass);

-- Name: horarios id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.horarios ALTER COLUMN id SET DEFAULT nextval('public.horarios_id_seq'::regclass);

-- Name: mensajes id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.mensajes ALTER COLUMN id SET DEFAULT nextval('public.mensajes_id_seq'::regclass);

-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);

-- Name: postulaciones id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.postulaciones ALTER COLUMN id SET DEFAULT nextval('public.postulaciones_id_seq'::regclass);

-- Name: reportes id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.reportes ALTER COLUMN id SET DEFAULT nextval('public.reportes_id_seq'::regclass);

-- Name: resenas id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.resenas ALTER COLUMN id SET DEFAULT nextval('public.resenas_id_seq'::regclass);

-- Name: solicitudes id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.solicitudes ALTER COLUMN id SET DEFAULT nextval('public.solicitudes_id_seq'::regclass);

-- Name: trabajo_horarios id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.trabajo_horarios ALTER COLUMN id SET DEFAULT nextval('public.trabajo_horarios_id_seq'::regclass);

-- Name: trabajos id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.trabajos ALTER COLUMN id SET DEFAULT nextval('public.trabajos_id_seq'::regclass);

-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);

INSERT INTO public.conversaciones VALUES (1, 11, 2, 4, NULL, '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.conversaciones VALUES (2, 10, 6, NULL, 1, '2026-11-16 19:25:35', '2026-11-16 19:25:35');

INSERT INTO public.habitacion_fotos VALUES (1, 1, 'img/habitaciones/galeria/dormitorio-02.jpg', 'Escritorio junto a la ventana', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (2, 1, 'img/habitaciones/galeria/bano-03.jpg', 'Baño compartido con terma', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (3, 1, 'img/habitaciones/galeria/cocina-02.jpg', 'Cocina de la casa', 3, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (4, 2, 'img/habitaciones/galeria/bano-01.jpg', 'Baño compartido', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (5, 2, 'img/habitaciones/galeria/lavanderia-01.jpg', 'Zona de lavado', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (6, 3, 'img/habitaciones/galeria/bano-02.jpg', 'Baño propio con ducha', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (7, 3, 'img/habitaciones/galeria/dormitorio-11.jpg', 'Otra vista del cuarto', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (8, 3, 'img/habitaciones/galeria/estudio-01.jpg', 'Zona de estudio', 3, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (9, 4, 'img/habitaciones/galeria/cocina-05.jpg', 'Cocina compartida', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (10, 4, 'img/habitaciones/galeria/dormitorio-07.jpg', 'Cama y ropero', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (11, 5, 'img/habitaciones/galeria/cocina-01.jpg', 'Kitchenette', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (12, 5, 'img/habitaciones/galeria/bano-02.jpg', 'Baño propio', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (13, 5, 'img/habitaciones/galeria/dormitorio-05.jpg', 'Dormitorio', 3, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (14, 5, 'img/habitaciones/galeria/estudio-01.jpg', 'Sala de estudio', 4, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (15, 6, 'img/habitaciones/galeria/dormitorio-13.jpg', 'Cada uno con su cama', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (16, 6, 'img/habitaciones/galeria/lavanderia-01.jpg', 'Lavadora de la casa', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (17, 7, 'img/habitaciones/galeria/dormitorio-09.jpg', 'Ventana a la calle', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (18, 7, 'img/habitaciones/galeria/bano-01.jpg', 'Baño con agua caliente', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (19, 8, 'img/habitaciones/galeria/lavanderia-01.jpg', 'Lavandería', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (20, 8, 'img/habitaciones/galeria/dormitorio-06.jpg', 'Ropero y cama', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (21, 9, 'img/habitaciones/galeria/dormitorio-14.jpg', 'Mucha luz por la mañana', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (22, 9, 'img/habitaciones/galeria/bano-03.jpg', 'Baño del piso', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (23, 11, 'img/habitaciones/galeria/estudio-01.jpg', 'Escritorio amplio', 1, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (24, 11, 'img/habitaciones/galeria/bano-01.jpg', 'Baño del piso', 2, '2026-11-16 19:25:37', '2026-11-16 19:25:37');
INSERT INTO public.habitacion_fotos VALUES (25, 11, 'img/habitaciones/galeria/cocina-04.jpg', 'Cocina compartida', 3, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (26, 12, 'img/habitaciones/galeria/bano-03.jpg', 'Baño con terma', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (27, 12, 'img/habitaciones/galeria/dormitorio-10.jpg', 'Otra vista', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (28, 13, 'img/habitaciones/galeria/dormitorio-08.jpg', 'Cama de plaza y media', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (29, 13, 'img/habitaciones/galeria/bano-01.jpg', 'Baño compartido', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (30, 13, 'img/habitaciones/galeria/cocina-03.jpg', 'Cocina', 3, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (31, 14, 'img/habitaciones/galeria/cocina-02.jpg', 'Kitchenette equipada', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (32, 14, 'img/habitaciones/galeria/bano-02.jpg', 'Baño propio', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (33, 14, 'img/habitaciones/galeria/estudio-01.jpg', 'Zona de estudio', 3, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (34, 15, 'img/habitaciones/galeria/bano-03.jpg', 'Baño compartido', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (35, 16, 'img/habitaciones/galeria/bano-01.jpg', 'Baño con terma', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (36, 16, 'img/habitaciones/galeria/cocina-04.jpg', 'Cocina compartida', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (37, 16, 'img/habitaciones/galeria/dormitorio-13.jpg', 'Ropero empotrado', 3, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (38, 17, 'img/habitaciones/galeria/lavanderia-01.jpg', 'Lavadora', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (39, 17, 'img/habitaciones/galeria/cocina-05.jpg', 'Cocina', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (40, 18, 'img/habitaciones/galeria/bano-02.jpg', 'Baño propio', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (41, 18, 'img/habitaciones/galeria/dormitorio-02.jpg', 'Escritorio', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (42, 19, 'img/habitaciones/galeria/bano-03.jpg', 'Baño compartido', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (43, 20, 'img/habitaciones/galeria/bano-01.jpg', 'Baño compartido', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (44, 21, 'img/habitaciones/galeria/dormitorio-11.jpg', 'Ventana con vista a los techos', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (45, 21, 'img/habitaciones/galeria/bano-03.jpg', 'Baño con terma', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (46, 22, 'img/habitaciones/galeria/cocina-03.jpg', 'Cocina compartida', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (47, 22, 'img/habitaciones/galeria/bano-01.jpg', 'Baño', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (48, 23, 'img/habitaciones/galeria/cocina-01.jpg', 'Cocina', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (49, 23, 'img/habitaciones/galeria/bano-02.jpg', 'Baño propio', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (50, 23, 'img/habitaciones/galeria/dormitorio-03.jpg', 'Dormitorio', 3, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (51, 23, 'img/habitaciones/galeria/estudio-01.jpg', 'Escritorio', 4, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (52, 24, 'img/habitaciones/galeria/bano-01.jpg', 'Baño compartido', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (53, 25, 'img/habitaciones/galeria/lavanderia-01.jpg', 'Lavandería', 1, '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.habitacion_fotos VALUES (54, 25, 'img/habitaciones/galeria/cocina-05.jpg', 'Cocina', 2, '2026-11-16 19:25:38', '2026-11-16 19:25:38');

INSERT INTO public.habitaciones VALUES (1, 2, 'Habitación amoblada en Av. Pachacútec', 'Habitación amplia y luminosa en segundo piso, con escritorio, cama de 1.5 plazas y ropero. Ambiente tranquilo para estudiar. Ingreso independiente y paradero de buses a la UNSAAC en la esquina.', 'Wanchaq', 'Av. Pachacútec 506', 'UNSAAC', 380.00, 'individual', 'Agua y luz, Wifi, Amoblado, Agua caliente', 'img/habitaciones/h1.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5242495, -71.9696114, 14, 2, 'compartido', 380.00);
INSERT INTO public.habitaciones VALUES (2, 3, 'Cuarto económico en Santiago', 'Cuarto sencillo con cama, mesa y estante. Baño compartido con otro estudiante. A dos cuadras del Mercado de San Pedro y cerca de los paraderos de la Av. Ejército.', 'Santiago', 'Calle Tambopata 230, Urb. Roccopata', 'UNSAAC', 250.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/h2.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5238400, -71.9829900, 10, 1, 'compartido', 250.00);
INSERT INTO public.habitaciones VALUES (3, 4, 'Habitación con baño propio – San Sebastián', 'Habitación con baño propio y terma. Edificio con portón y vigilancia. A unos ocho minutos caminando del campus Larapa de la U. Andina.', 'San Sebastián', 'Calle Kiswar 145, Urb. Túpac Amaru', 'U. Andina', 450.00, 'individual', 'Agua y luz, Wifi, Baño propio, Agua caliente', 'img/habitaciones/h3.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5347800, -71.9087300, 16, 3, 'propio', 450.00);
INSERT INTO public.habitaciones VALUES (4, 2, 'Cuarto cerca de la UNSAAC', 'Cuarto en casa familiar a cinco minutos caminando de la UNSAAC y frente al Real Plaza. Se puede usar la cocina en horarios acordados.', 'Cusco', 'Pasaje Inca Roca 120, Urb. Manuel Prado', 'UNSAAC', 320.00, 'individual', 'Agua y luz, Wifi, Cocina', 'img/habitaciones/h4.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5207000, -71.9553900, 12, 2, 'compartido', 320.00);
INSERT INTO public.habitaciones VALUES (5, 4, 'Minidepartamento para estudiante', 'Minidepartamento con kitchenette, baño propio y sala de estudio. Ideal para quien busca privacidad. Cerca de la Vía Expresa y de la U. Andina.', 'San Sebastián', 'Urb. Túpac Amaru Mz. B Lt. 12', 'U. Andina', 550.00, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente', 'img/habitaciones/h5.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5387800, -71.9070200, 28, 2, 'propio', 550.00);
INSERT INTO public.habitaciones VALUES (6, 3, 'Habitación compartida en Wanchaq', 'Habitación para dos estudiantes, cada uno con su cama y escritorio. Zona segura, cerca del Mercado de Wanchaq y con tiendas alrededor.', 'Wanchaq', 'Jr. La Convención B-17', 'UNSAAC', 220.00, 'compartida', 'Agua y luz, Wifi, Lavandería', 'img/habitaciones/h6.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5274600, -71.9635100, 20, 1, 'compartido', 220.00);
INSERT INTO public.habitaciones VALUES (7, 2, 'Habitación amplia en el centro', 'Habitación grande en casona del barrio de San Cristóbal, con ventana a la calle y mucha luz natural. A cinco cuadras de la Plaza de Armas.', 'Cusco', 'Calle Pumacurco 490, San Cristóbal', 'UNSAAC', 400.00, 'individual', 'Agua y luz, Wifi, Agua caliente', 'img/habitaciones/h7.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5132800, -71.9780400, 18, 2, 'compartido', 400.00);
INSERT INTO public.habitaciones VALUES (8, 3, 'Cuarto para estudiante – Santiago', 'Cuarto con cama de plaza y media y ropero, frente al Hospital Antonio Lorena. Pasan varias líneas de buses hacia la Av. de la Cultura.', 'Santiago', 'Av. Antonio Lorena 780, Dignidad Nacional', 'UNSAAC', 270.00, 'individual', 'Agua y luz, Lavandería', 'img/habitaciones/h8.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5256400, -71.9882000, 11, 2, 'compartido', 270.00);
INSERT INTO public.habitaciones VALUES (9, 4, 'Cuarto con vista – San Jerónimo', 'Cuarto en tercer piso con vista a los cerros, cerca de la plaza de San Jerónimo. Buses directos a la U. Andina y a la U. Continental.', 'San Jerónimo', 'Calle Clorinda Matto de Turner 210', 'U. Andina', 280.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/h9.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5415800, -71.8838000, 12, 3, 'compartido', 280.00);
INSERT INTO public.habitaciones VALUES (10, 14, 'Cuarto amplio a solo S/ 150 en el centro', 'Cuarto grande, quedan pocos. Para separarlo deposita 300 soles al número de cuenta que te paso por mensaje.', 'Cusco', 'Mesón de la Estrella 175', 'UNSAAC', 150.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/h10.jpg', 'disponible', '2026-11-16 19:25:35', '2026-11-16 19:25:37', -13.5198000, -71.9802000, NULL, NULL, NULL, NULL);
INSERT INTO public.habitaciones VALUES (11, 15, 'Cuarto con escritorio frente al Real Plaza', 'Cuarto amoblado en tercer piso, con escritorio grande y buena señal de wifi. A cuatro cuadras de la UNSAAC y frente al Real Plaza.', 'Cusco', 'Av. Collasuyo 912, Urb. Manuel Prado', 'UNSAAC', 350.00, 'individual', 'Agua y luz, Wifi, Amoblado, Escritorio', 'img/habitaciones/galeria/dormitorio-03.jpg', 'disponible', '2026-11-16 19:25:37', '2026-11-16 19:25:37', -13.5205400, -71.9552700, 13, 3, 'compartido', 350.00);
INSERT INTO public.habitaciones VALUES (12, 15, 'Habitación en Mariscal Gamarra', 'Habitación en casa familiar al lado del Parque del Magisterio. Barrio tranquilo, ideal para estudiar. La UNSAAC queda a diez minutos caminando.', 'Cusco', 'Parque Magisterio 21, Urb. Mariscal Gamarra', 'UNSAAC', 300.00, 'individual', 'Agua y luz, Wifi, Agua caliente', 'img/habitaciones/galeria/dormitorio-12.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5211200, -71.9623000, 12, 2, 'compartido', 300.00);
INSERT INTO public.habitaciones VALUES (13, 16, 'Cuarto cerca de la U. Continental – Angostura', 'Cuarto a cinco minutos caminando del campus de la U. Continental en Angostura. Sin perder tiempo en el tráfico de la Vía Expresa.', 'Saylla', 'Sector Angostura, Mz. C Lt. 8', 'U. Continental', 300.00, 'individual', 'Agua y luz, Wifi, Agua caliente', 'img/habitaciones/galeria/dormitorio-01.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5551700, -71.8558700, 14, 2, 'compartido', 300.00);
INSERT INTO public.habitaciones VALUES (14, 16, 'Minidepartamento en Saylla', 'Minidepartamento nuevo con cocina, baño propio y espacio para bicicleta o moto. A cinco minutos de la U. Continental.', 'Saylla', 'Carretera Cusco–Urcos km 10', 'U. Continental', 520.00, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente, Cochera', 'img/habitaciones/galeria/dormitorio-04.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5565700, -71.8548700, 30, 1, 'propio', 520.00);
INSERT INTO public.habitaciones VALUES (15, 16, 'Habitación en Oropesa Huaylla', 'Cuarto sencillo en casa nueva, a unos diez minutos caminando de la U. Continental. Hay tiendas y restaurante de menú en la misma cuadra.', 'San Jerónimo', 'Asoc. Oropesa Huaylla Mz. F Lt. 3', 'U. Continental', 260.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/galeria/dormitorio-06.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5515400, -71.8584000, 11, 2, 'compartido', 260.00);
INSERT INTO public.habitaciones VALUES (16, 17, 'Cuarto en Larapa a pasos de la U. Andina', 'Cuarto amoblado a dos cuadras del campus Larapa de la U. Andina. Casa con portón eléctrico y patio.', 'San Jerónimo', 'Av. Villa del Carmen 340, La Arboleda', 'U. Andina', 330.00, 'individual', 'Agua y luz, Wifi, Amoblado, Agua caliente', 'img/habitaciones/galeria/dormitorio-07.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5375400, -71.9020900, 13, 2, 'compartido', 330.00);
INSERT INTO public.habitaciones VALUES (17, 17, 'Habitación compartida en El Tingo', 'Habitación para dos estudiantes de la U. Andina. Cada uno tiene su cama, escritorio y repisa. Lavadora de uso común.', 'San Jerónimo', 'Calle Manantial 118, El Tingo', 'U. Andina', 200.00, 'compartida', 'Agua y luz, Wifi, Lavandería', 'img/habitaciones/galeria/dormitorio-10.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5392700, -71.9022300, 18, 1, 'compartido', 200.00);
INSERT INTO public.habitaciones VALUES (18, 17, 'Cuarto con baño propio en Larapita', 'Cuarto con baño propio en edificio de tres pisos, a cinco minutos del campus Larapa. Agua caliente todo el día.', 'San Jerónimo', 'Av. Collasuyo 1520, Urb. Larapita', 'U. Andina', 420.00, 'individual', 'Agua y luz, Wifi, Baño propio, Agua caliente', 'img/habitaciones/galeria/dormitorio-05.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5354400, -71.9022900, 15, 3, 'propio', 420.00);
INSERT INTO public.habitaciones VALUES (19, 4, 'Cuarto cerca del Instituto Túpac Amaru', 'Cuarto en casa familiar a cinco minutos del Instituto Túpac Amaru. Zona residencial, con paraderos sobre la Av. de la Cultura.', 'San Sebastián', 'Jr. Las Magnolias 205, Señor de Exaltación', 'IST Túpac Amaru', 260.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/galeria/dormitorio-08.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5354900, -71.9263500, 11, 2, 'compartido', 260.00);
INSERT INTO public.habitaciones VALUES (20, 3, 'Cuarto económico en Ayarmaca', 'Cuarto sencillo en primer piso, con cama y mesa. Buena opción si buscas pagar poco y estudias en San Sebastián.', 'San Sebastián', 'Calle Mariscal Cáceres 77, Ayarmaca', 'IST Túpac Amaru', 230.00, 'individual', 'Agua y luz', 'img/habitaciones/galeria/dormitorio-09.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5363500, -71.9368000, 10, 1, 'compartido', 230.00);
INSERT INTO public.habitaciones VALUES (21, 2, 'Habitación con vista en San Blas', 'Habitación en casona del barrio de San Blas, con vista a los techos del centro histórico. Calle peatonal y tranquila.', 'Cusco', 'Cuesta de San Blas 318', 'UNSAAC', 420.00, 'individual', 'Agua y luz, Wifi, Agua caliente, Amoblado', 'img/habitaciones/galeria/dormitorio-14.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5148000, -71.9750000, 15, 2, 'compartido', 420.00);
INSERT INTO public.habitaciones VALUES (22, 15, 'Cuarto en Wanchaq cerca del mercado', 'Cuarto en Wanchaq a una cuadra de la Av. de la Cultura. Mercado, farmacias y bancos alrededor.', 'Wanchaq', 'Av. Huayruropata 1205', 'UNSAAC', 300.00, 'individual', 'Agua y luz, Wifi, Cocina', 'img/habitaciones/galeria/dormitorio-11.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5247200, -71.9645200, 12, 2, 'compartido', 300.00);
INSERT INTO public.habitaciones VALUES (23, 15, 'Minidepartamento en Av. Garcilaso', 'Minidepartamento completo en cuarto piso: dormitorio, kitchenette y baño propio. Ideal para compartir entre hermanos.', 'Wanchaq', 'Av. Garcilaso 615', 'UNSAAC', 600.00, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente, Cable', 'img/habitaciones/galeria/dormitorio-13.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5219300, -71.9709200, 32, 4, 'propio', 600.00);
INSERT INTO public.habitaciones VALUES (24, 3, 'Cuarto junto a la Plaza Belén', 'Cuarto frente a la Plaza Belén, en Santiago. Precio cómodo y a diez minutos en bus del centro.', 'Santiago', 'Plaza Belén 14, Urb. Roccopata', 'UNSAAC', 240.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/galeria/dormitorio-15.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5267500, -71.9808300, 10, 2, 'compartido', 240.00);
INSERT INTO public.habitaciones VALUES (25, 17, 'Cuarto cerca del Mercado Vinocanchón', 'Cuarto a tres cuadras del Mercado Vinocanchón. Buses a la U. Andina y a la U. Continental en la misma avenida.', 'San Jerónimo', 'Calle Arica 330, Pata Pata', 'U. Andina', 250.00, 'individual', 'Agua y luz, Wifi, Lavandería', 'img/habitaciones/galeria/dormitorio-16.jpg', 'disponible', '2026-11-16 19:25:38', '2026-11-16 19:25:38', -13.5445400, -71.8830300, 11, 2, 'compartido', 250.00);

INSERT INTO public.horarios VALUES (1, 10, 'lunes', 'manana');
INSERT INTO public.horarios VALUES (2, 10, 'martes', 'manana');
INSERT INTO public.horarios VALUES (3, 10, 'miercoles', 'manana');
INSERT INTO public.horarios VALUES (4, 10, 'jueves', 'manana');
INSERT INTO public.horarios VALUES (5, 10, 'viernes', 'manana');
INSERT INTO public.horarios VALUES (6, 10, 'lunes', 'tarde');
INSERT INTO public.horarios VALUES (7, 10, 'miercoles', 'tarde');

INSERT INTO public.mensajes VALUES (1, 1, 11, 'Hola señora Rosa, ¿el cuarto tiene escritorio?', true, '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.mensajes VALUES (2, 1, 2, 'Hola Ana, sí tiene escritorio y una silla. Te espero el día de la visita.', true, '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.mensajes VALUES (3, 1, 11, 'Perfecto, muchas gracias.', false, '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.mensajes VALUES (4, 2, 10, 'Buenas tardes, ¿qué días empezaría?', true, '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.mensajes VALUES (5, 2, 6, 'Hola Luis, puedes empezar este martes a las 2 pm en el local de Santiago.', false, '2026-11-16 19:25:35', '2026-11-16 19:25:35');

INSERT INTO public.migrations VALUES (1, '2026_11_01_000001_create_usuarios_table', 1);
INSERT INTO public.migrations VALUES (2, '2026_11_01_000002_create_habitaciones_table', 1);
INSERT INTO public.migrations VALUES (3, '2026_11_01_000003_create_trabajos_table', 1);
INSERT INTO public.migrations VALUES (4, '2026_11_15_000001_create_modulos_finales', 1);
INSERT INTO public.migrations VALUES (5, '2026_11_16_000001_create_contactos_y_sesiones', 1);
INSERT INTO public.migrations VALUES (6, '2026_11_17_000001_create_eventos_seguridad', 1);
INSERT INTO public.migrations VALUES (7, '2026_11_18_000001_create_identidad', 1);
INSERT INTO public.migrations VALUES (8, '2026_11_19_000001_create_galeria_y_detalles', 1);

INSERT INTO public.notifications VALUES ('4d02afbb-1672-4dd3-8522-7d894e1b6fed', 'App\Notifications\Aviso', 'App\Models\Usuario', 10, '{"texto":"Minimarket Wayra acept\u00f3 tu postulaci\u00f3n a \"Repartidor en bicicleta\"","url":"\/estudiante","icono":"briefcase"}', NULL, '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.notifications VALUES ('a6ce7ac9-40fc-47da-9f27-45b0167b9b20', 'App\Notifications\Aviso', 'App\Models\Usuario', 3, '{"texto":"Ana Quispe Ccasa quiere visitar \"Habitaci\u00f3n compartida en Wanchaq\"","url":"\/arrendador\/solicitudes","icono":"house-door"}', NULL, '2026-11-16 19:25:35', '2026-11-16 19:25:35');

INSERT INTO public.postulaciones VALUES (1, 1, 10, 'Tengo bicicleta propia y conozco bien Santiago.', 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.postulaciones VALUES (2, 6, 10, 'Tengo inglés básico.', 'pendiente', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.postulaciones VALUES (3, 5, 11, 'Estoy en sexto ciclo de Ingeniería de Sistemas.', 'pendiente', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.postulaciones VALUES (4, 4, 13, NULL, 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.postulaciones VALUES (5, 1, 12, NULL, 'rechazada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.postulaciones VALUES (6, 13, 21, 'Ingresé a Ingeniería Civil por examen y ya enseñé a mis primos.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.postulaciones VALUES (7, 8, 22, 'Trabajé en la cafetería de mi tío en Urubamba.', 'pendiente', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.postulaciones VALUES (8, 11, 23, 'Tengo inglés intermedio y estudio de día.', 'pendiente', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.postulaciones VALUES (9, 18, 12, NULL, 'pendiente', '2026-11-16 19:25:38', '2026-11-16 19:25:38');

INSERT INTO public.reportes VALUES (1, 13, 10, NULL, 'Pide adelanto sin visita', 'Me pidió depositar 300 soles para separar el cuarto sin dejarme verlo.', 'pendiente', '2026-11-16 19:25:35', '2026-11-16 19:25:35');

INSERT INTO public.resenas VALUES (1, 1, 10, 5, 'La habitación es igual que en las fotos. La señora Rosa es muy amable y la zona es tranquila.', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.resenas VALUES (2, 1, 12, 4, 'Buena habitación y cerca de la universidad. El wifi a veces falla en las noches.', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.resenas VALUES (3, 3, 13, 5, 'El baño propio y el agua caliente valen la pena. Muy seguro el edificio.', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.resenas VALUES (4, 2, 12, 3, 'Es económico pero el baño compartido es pequeño. Para el precio está bien.', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.resenas VALUES (5, 13, 21, 5, 'Llego a clases en cinco minutos. El señor Fredy es muy atento y el agua caliente funciona bien.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.resenas VALUES (6, 16, 22, 5, 'Muy cerca de la universidad y la casa es segura. Las fotos son reales.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.resenas VALUES (7, 11, 23, 4, 'Buen escritorio y buen wifi. Por las noches hay algo de ruido de la avenida.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.resenas VALUES (8, 17, 22, 4, 'Compartí con una compañera de la U. Andina y nos fue bien. La lavadora ayuda mucho.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.resenas VALUES (9, 14, 21, 5, 'Tiene todo lo necesario y está al lado de la Continental. Vale lo que cuesta.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.resenas VALUES (10, 21, 23, 5, 'La vista es increíble y el barrio es tranquilo. Hay que subir escaleras, eso sí.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.resenas VALUES (11, 12, 11, 4, 'Zona tranquila y cerca de la UNSAAC. La familia es respetuosa.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.resenas VALUES (12, 19, 13, 3, 'Está bien para el precio. El baño compartido se llena por las mañanas.', '2026-11-16 19:25:38', '2026-11-16 19:25:38');

INSERT INTO public.solicitudes VALUES (1, 4, 11, '2026-11-18', 'Hola, soy de Abancay y estudio en la UNSAAC. ¿Puedo ir el sábado?', 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.solicitudes VALUES (2, 6, 11, '2026-11-20', '¿La habitación compartida es solo para mujeres?', 'pendiente', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.solicitudes VALUES (3, 1, 10, '2026-11-10', 'Quisiera verla antes de mudarme.', 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.solicitudes VALUES (4, 1, 12, '2026-11-06', 'Hola, estudio en la Continental.', 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.solicitudes VALUES (5, 3, 13, '2026-11-08', '¿Puedo ir por la tarde?', 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.solicitudes VALUES (6, 2, 12, '2026-11-04', 'Me interesa, ¿sigue disponible?', 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.solicitudes VALUES (7, 7, 11, '2026-11-13', 'Hola, ¿puedo verla el lunes?', 'aceptada', '2026-11-16 19:25:35', '2026-11-16 19:25:35');
INSERT INTO public.solicitudes VALUES (8, 13, 21, '2026-11-01', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (9, 16, 22, '2026-10-28', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (10, 11, 23, '2026-11-06', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (11, 17, 22, '2026-11-06', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (12, 14, 21, '2026-10-30', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (13, 21, 23, '2026-10-27', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (14, 12, 11, '2026-11-09', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (15, 19, 13, '2026-10-31', 'Me gustaría ver el cuarto antes de decidir.', 'aceptada', '2026-11-16 19:25:38', '2026-11-16 19:25:38');
INSERT INTO public.solicitudes VALUES (16, 23, 21, '2026-11-19', '¿Se puede compartir entre dos hermanos?', 'pendiente', '2026-11-16 19:25:38', '2026-11-16 19:25:38');

INSERT INTO public.trabajo_horarios VALUES (1, 1, 'martes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (2, 1, 'jueves', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (3, 1, 'sabado', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (4, 2, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (5, 3, 'lunes', 'manana');
INSERT INTO public.trabajo_horarios VALUES (6, 3, 'martes', 'manana');
INSERT INTO public.trabajo_horarios VALUES (7, 3, 'miercoles', 'manana');
INSERT INTO public.trabajo_horarios VALUES (8, 3, 'jueves', 'manana');
INSERT INTO public.trabajo_horarios VALUES (9, 3, 'viernes', 'manana');
INSERT INTO public.trabajo_horarios VALUES (10, 4, 'lunes', 'noche');
INSERT INTO public.trabajo_horarios VALUES (11, 4, 'martes', 'noche');
INSERT INTO public.trabajo_horarios VALUES (12, 4, 'miercoles', 'noche');
INSERT INTO public.trabajo_horarios VALUES (13, 4, 'jueves', 'noche');
INSERT INTO public.trabajo_horarios VALUES (14, 4, 'viernes', 'noche');
INSERT INTO public.trabajo_horarios VALUES (15, 5, 'lunes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (16, 5, 'miercoles', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (17, 5, 'viernes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (18, 6, 'sabado', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (19, 6, 'sabado', 'noche');
INSERT INTO public.trabajo_horarios VALUES (20, 6, 'domingo', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (21, 6, 'domingo', 'noche');
INSERT INTO public.trabajo_horarios VALUES (22, 7, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (23, 7, 'sabado', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (24, 8, 'lunes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (25, 8, 'miercoles', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (26, 8, 'viernes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (27, 8, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (28, 9, 'viernes', 'noche');
INSERT INTO public.trabajo_horarios VALUES (29, 9, 'sabado', 'noche');
INSERT INTO public.trabajo_horarios VALUES (30, 10, 'martes', 'manana');
INSERT INTO public.trabajo_horarios VALUES (31, 10, 'jueves', 'manana');
INSERT INTO public.trabajo_horarios VALUES (32, 10, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (33, 11, 'lunes', 'noche');
INSERT INTO public.trabajo_horarios VALUES (34, 11, 'miercoles', 'noche');
INSERT INTO public.trabajo_horarios VALUES (35, 11, 'viernes', 'noche');
INSERT INTO public.trabajo_horarios VALUES (36, 12, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (37, 12, 'domingo', 'manana');
INSERT INTO public.trabajo_horarios VALUES (38, 13, 'martes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (39, 13, 'jueves', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (40, 13, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (41, 14, 'lunes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (42, 14, 'miercoles', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (43, 15, 'sabado', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (44, 15, 'domingo', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (45, 16, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (46, 16, 'sabado', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (47, 16, 'domingo', 'manana');
INSERT INTO public.trabajo_horarios VALUES (48, 17, 'lunes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (49, 17, 'martes', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (50, 17, 'jueves', 'tarde');
INSERT INTO public.trabajo_horarios VALUES (51, 18, 'sabado', 'manana');
INSERT INTO public.trabajo_horarios VALUES (52, 18, 'domingo', 'manana');

INSERT INTO public.trabajos VALUES (1, 6, 'Repartidor en bicicleta', 'Minimarket Wayra', 'Reparto de pedidos en la zona de Santiago. Se da la bicicleta.', 'Santiago', 'medio tiempo', 40.00, 'dia', 'activa', '2026-11-16 19:25:35', '2026-11-16 19:25:38', 'Calle Los Ángeles 1265, Urb. Roccopata', -13.5262800, -71.9848400, 'Tener bicicleta o saber manejarla
Conocer las calles de Santiago
Mayor de 18 años', 2);
INSERT INTO public.trabajos VALUES (2, 7, 'Soporte técnico sábados', 'Andes Tech SAC', 'Mantenimiento de computadoras e instalación de programas.', 'Wanchaq', 'por horas', 70.00, 'dia', 'activa', '2026-11-16 19:25:35', '2026-11-16 19:25:38', 'Av. Tomasa Tito Condemayta 718', -13.5241973, -71.9647209, 'Estudiar Ingeniería de Sistemas o afines
Saber instalar Windows y armar computadoras', 1);
INSERT INTO public.trabajos VALUES (3, 8, 'Ayudante de cocina (mañanas)', 'Restaurante Inti Raymi', 'Apoyo en la preparación de desayunos y almuerzos.', 'Cusco', 'medio tiempo', 50.00, 'dia', 'activa', '2026-11-16 19:25:35', '2026-11-16 19:25:38', 'Calle Procuradores 344', -13.5155052, -71.9794839, 'Carné de sanidad (te ayudamos a sacarlo)
Puntualidad', 2);
INSERT INTO public.trabajos VALUES (4, 6, 'Atención en minimarket (noches)', 'Minimarket Wayra', 'Atención al cliente y caja en turno de noche.', 'San Sebastián', 'medio tiempo', 45.00, 'dia', 'activa', '2026-11-16 19:25:35', '2026-11-16 19:25:38', 'Av. de la Cultura s/n, frente a la plaza de San Sebastián', -13.5300534, -71.9373475, 'Facilidad para atender al público
Manejo básico de caja', 1);
INSERT INTO public.trabajos VALUES (5, 7, 'Practicante de sistemas', 'Andes Tech SAC', 'Apoyo en desarrollo web y soporte a usuarios. Para estudiantes de Ingeniería de Sistemas.', 'Wanchaq', 'practicas', 1025.00, 'mes', 'activa', '2026-11-16 19:25:35', '2026-11-16 19:25:38', 'Av. Tomasa Tito Condemayta 718', -13.5241973, -71.9647209, 'Estudiante de sexto ciclo en adelante
Conocer HTML, CSS y algo de PHP o JavaScript', 1);
INSERT INTO public.trabajos VALUES (6, 8, 'Mozo/a fin de semana', 'Restaurante Inti Raymi', 'Atención en mesas los fines de semana. Se valora inglés básico.', 'Cusco', 'medio tiempo', 60.00, 'dia', 'activa', '2026-11-16 19:25:35', '2026-11-16 19:25:38', 'Calle Procuradores 344', -13.5155052, -71.9794839, 'Inglés básico para atender turistas
Buena presencia y trato amable', 3);
INSERT INTO public.trabajos VALUES (7, 9, 'Guía de apoyo turístico', 'Qhapaq Tours', 'Acompañar a grupos de turistas en el centro histórico.', 'Cusco', 'por horas', 80.00, 'dia', 'activa', '2026-11-16 19:25:35', '2026-11-16 19:25:38', 'Plaza San Blas 102', -13.5151764, -71.9742158, 'Estudiante de Turismo
Inglés intermedio', 2);
INSERT INTO public.trabajos VALUES (8, 18, 'Barista medio tiempo', 'Café Kuntur', 'Preparar cafés, jugos y postres y atender la barra. Te enseñamos a usar la máquina de espresso.', 'Cusco', 'medio tiempo', 45.00, 'dia', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Av. El Sol 520', -13.5227515, -71.9745808, 'Ganas de aprender
Trato amable con el público', 2);
INSERT INTO public.trabajos VALUES (9, 18, 'Ayudante de barra (noches de fin de semana)', 'Café Kuntur', 'Apoyo en barra y limpieza los viernes y sábados por la noche.', 'Cusco', 'por horas', 12.00, 'hora', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Av. El Sol 520', -13.5227515, -71.9745808, 'Mayor de 18 años
Disponibilidad viernes y sábado', 2);
INSERT INTO public.trabajos VALUES (10, 18, 'Ayudante en juguería del mercado', 'Café Kuntur', 'Preparar jugos y atender a los clientes del puesto en el Mercado de Wanchaq.', 'Wanchaq', 'medio tiempo', 35.00, 'dia', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Mercado de Wanchaq, puesto 48', -13.5312589, -71.9603152, 'Puntualidad
Carné de sanidad', 1);
INSERT INTO public.trabajos VALUES (11, 19, 'Recepcionista de noche en hostal', 'Hostal Ñusta Wasi', 'Recibir a los huéspedes, hacer check-in y atender el teléfono. Hay tiempo para estudiar en el turno.', 'Cusco', 'medio tiempo', 50.00, 'dia', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Cuesta de San Blas 310', -13.5150500, -71.9748000, 'Inglés básico o intermedio
Responsabilidad', 1);
INSERT INTO public.trabajos VALUES (12, 19, 'Apoyo en desayunos (fines de semana)', 'Hostal Ñusta Wasi', 'Servir desayunos y ordenar el comedor del hostal los sábados y domingos.', 'Cusco', 'medio tiempo', 40.00, 'dia', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Cuesta de San Blas 310', -13.5150500, -71.9748000, 'Puntualidad
Orden y limpieza', 2);
INSERT INTO public.trabajos VALUES (13, 20, 'Profesor/a de matemática para preuniversitarios', 'Academia Saqsay', 'Dictar clases de álgebra y aritmética a grupos de 15 alumnos que se preparan para el examen de admisión.', 'Wanchaq', 'por horas', 25.00, 'hora', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Av. Pachacútec 615', -13.5244740, -71.9704044, 'Estudiante de ingeniería, matemática o educación
Haber ingresado a la universidad por examen', 3);
INSERT INTO public.trabajos VALUES (14, 20, 'Tutor/a de inglés para escolares', 'Academia Saqsay', 'Reforzar inglés a escolares de secundaria en grupos pequeños.', 'Wanchaq', 'por horas', 20.00, 'hora', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Av. Pachacútec 615', -13.5244740, -71.9704044, 'Inglés intermedio o avanzado
Paciencia con adolescentes', 2);
INSERT INTO public.trabajos VALUES (15, 20, 'Promotor/a de matrícula en Real Plaza', 'Academia Saqsay', 'Informar sobre los ciclos de la academia en un módulo dentro del centro comercial.', 'Cusco', 'por horas', 50.00, 'dia', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Real Plaza Cusco, Av. Collasuyo 2964', -13.5234656, -71.9502285, 'Facilidad de palabra
Disponibilidad los domingos', 4);
INSERT INTO public.trabajos VALUES (16, 6, 'Cajero/a fin de semana en San Jerónimo', 'Minimarket Wayra', 'Atención en caja y reposición de productos en nuestro local de San Jerónimo.', 'San Jerónimo', 'medio tiempo', 45.00, 'dia', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Calle Agricultura 120, frente al Mercado Vinocanchón', -13.5435425, -71.8877752, 'Manejo básico de caja
Vivir cerca de San Jerónimo es un plus', 1);
INSERT INTO public.trabajos VALUES (17, 7, 'Community manager (medio tiempo)', 'Andes Tech SAC', 'Manejar las redes sociales de nuestros clientes: publicaciones, diseño básico y respuestas a mensajes.', 'Wanchaq', 'medio tiempo', 900.00, 'mes', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Av. Tomasa Tito Condemayta 718', -13.5241973, -71.9647209, 'Estudiante de Comunicación, Marketing o Diseño
Manejo de Canva', 1);
INSERT INTO public.trabajos VALUES (18, 7, 'Encuestador/a para estudio de mercado', 'Andes Tech SAC', 'Aplicar encuestas en celular a vecinos de San Sebastián durante dos fines de semana.', 'San Sebastián', 'por horas', 50.00, 'dia', 'activa', '2026-11-16 19:25:38', '2026-11-16 19:25:38', 'Plaza de Armas de San Sebastián', -13.5300534, -71.9373475, 'Celular con datos
Trato amable', 6);

INSERT INTO public.usuarios VALUES (1, 'Administrador TAMPU', 'admin@tampu.pe', '70000001', NULL, NULL, 'admin', 'no_aplica', NULL, '$2y$12$6poRyL0VjsfjaoGfnNvZO.yUj3HTpAQ4PoysMIQZu09Oe/OOqGZ6y', NULL, '2026-11-16 19:25:31', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (2, 'Rosa Huamán Quispe', 'rosa@tampu.pe', '41234567', '984111222', NULL, 'arrendador', 'verificado', NULL, '$2y$12$EI5xnwu82/JBVVTszFvdlu2MtwVC4EZfGz9oYLCEUrpvdQOQHVpeq', NULL, '2026-11-16 19:25:32', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (3, 'Julio Ccori Mamani', 'julio@tampu.pe', '42345678', '984333444', NULL, 'arrendador', 'verificado', NULL, '$2y$12$Se0T1buDjcbOfJjsNYGYIeioj45xkrNVvcnzk/pZmF1uXI43i7Iyq', NULL, '2026-11-16 19:25:32', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (4, 'Carmen Mamani Huallpa', 'carmen@tampu.pe', '43456789', '984555666', NULL, 'arrendador', 'verificado', NULL, '$2y$12$Md.HOyS3/aVLp0h0diZMQ.G.tfNetCuMvOSfkT3o.75SeKiI6QdH2', NULL, '2026-11-16 19:25:32', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (5, 'Pedro Ccahuana Rojas', 'pedro@tampu.pe', '44567890', '984777888', NULL, 'arrendador', 'pendiente', 'documentos/demo-pedro.png', '$2y$12$plA7odxhByHj4smk5gxjfuF0xXy9zz3rpupS6psyzkqWwhbUWUaKS', NULL, '2026-11-16 19:25:32', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (6, 'Lucía Ttito Condori', 'wayra@tampu.pe', '45678901', '974111222', NULL, 'empleador', 'verificado', NULL, '$2y$12$8D8AJ.hyukF5wQfYZ9WbU.JcxbulScz7JSlAOeVtEEeMh86Z.yJw2', NULL, '2026-11-16 19:25:33', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (7, 'Marco Paredes Soto', 'andestech@tampu.pe', '46789012', '974333444', NULL, 'empleador', 'verificado', NULL, '$2y$12$nsRw8IoWlmBhavecjGSd.Ohsk2DZ1No/PLCLQzkzXVREWTFZoSpKG', NULL, '2026-11-16 19:25:33', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (8, 'Elena Condori Apaza', 'intiraymi@tampu.pe', '47890123', '974555666', NULL, 'empleador', 'verificado', NULL, '$2y$12$GG.ETF7JNiXNL8AfDI5D0emvXvJrGzftmw/rtD5j9u05IL.y2TtZS', NULL, '2026-11-16 19:25:33', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (9, 'Diego Huallpa Cusi', 'qhapaq@tampu.pe', '48901234', '974777888', NULL, 'empleador', 'pendiente', 'documentos/demo-qhapaq.png', '$2y$12$QuOC0EDcd3tb0P3O/fJYsOEo17XwnNboupXl8DiLfbjl11mXaeHOW', NULL, '2026-11-16 19:25:33', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (10, 'Luis Mamani Choque', 'luis@tampu.pe', '72345678', '951222333', 'Sicuani', 'estudiante', 'no_aplica', NULL, '$2y$12$1ZbbU3QF3C/g4NnSGzRjwutxZ76k77rLwcCNDVvRdkGZK9r.phQyu', NULL, '2026-11-16 19:25:34', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (11, 'Ana Quispe Ccasa', 'ana@tampu.pe', '73456789', '951444555', 'Abancay', 'estudiante', 'no_aplica', NULL, '$2y$12$nwKuOrXJC7Ij/AdVkyjaEOIbzEaFyqv6H2eEAr9l0/wS8Pt/O.cwK', NULL, '2026-11-16 19:25:34', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (12, 'Rocío Apaza Layme', 'rocio@tampu.pe', '74567890', '951666777', 'Puno', 'estudiante', 'no_aplica', NULL, '$2y$12$QI0d/pzAP4Jc7muAo0EDAeeXKfHCFAqE1fFVBdKB/egCQBHnQfUn6', NULL, '2026-11-16 19:25:34', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (13, 'Kevin Huamán Cusi', 'kevin@tampu.pe', '75678901', '951888999', 'Quillabamba', 'estudiante', 'no_aplica', NULL, '$2y$12$4sDt/IxNHckbuEfwh0t28uyAu0NU3aGhA0yGgLbT7eHx8w4bh0Tga', NULL, '2026-11-16 19:25:35', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (14, 'Mario Sullca Ttito', 'mario@tampu.pe', '49012345', '984999000', NULL, 'arrendador', 'verificado', NULL, '$2y$12$uD/c4Vax4k3QZuuT/ji8m.El7Cx6U.rZH0RTQuKTucbR0JmK8FiRa', NULL, '2026-11-16 19:25:35', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (15, 'Hilda Quispe Ttito', 'hilda@tampu.pe', '41987654', '984222111', NULL, 'arrendador', 'verificado', NULL, '$2y$12$4fCBrhKczeczGLP6XvowLOpyA/ZY0fBA6bRdVtX8Pxi9P1sFfuZsW', NULL, '2026-11-16 19:25:35', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (16, 'Fredy Callañaupa Huamán', 'fredy@tampu.pe', '42876543', '984444333', NULL, 'arrendador', 'verificado', NULL, '$2y$12$itaqmBebEwiUuPhAVuI3aO3L3JICqxpcIl6p/9lNFAUll3nmVBBQ.', NULL, '2026-11-16 19:25:35', '2026-11-16 19:25:35', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (17, 'Norma Álvarez Puma', 'norma@tampu.pe', '43765432', '984666555', NULL, 'arrendador', 'verificado', NULL, '$2y$12$b.5el42si.en8rJOY7ldYe2qemBxoafuwpEHC9Q1P9EMR8LLcn/V6', NULL, '2026-11-16 19:25:36', '2026-11-16 19:25:36', NULL, false, '2026-11-16 19:25:35', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (18, 'Raúl Quispe Mayta', 'kuntur@tampu.pe', '45123987', '974222111', NULL, 'empleador', 'verificado', NULL, '$2y$12$pbqMZ0JPN5ng321TnEQHNODRx7KIZ4LfMCmeYun12zbyQjemkJ34a', NULL, '2026-11-16 19:25:36', '2026-11-16 19:25:36', NULL, false, '2026-11-16 19:25:36', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (19, 'Patricia Rojas Huamán', 'nustawasi@tampu.pe', '46234876', '974444333', NULL, 'empleador', 'verificado', NULL, '$2y$12$xEZNGEUK/XsLmFUEzEzlled9f0hXKyn1tmLkSeYueU.q3YJmlWxFm', NULL, '2026-11-16 19:25:36', '2026-11-16 19:25:36', NULL, false, '2026-11-16 19:25:36', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (20, 'Sonia Choque Pari', 'saqsay@tampu.pe', '47345765', '974666555', NULL, 'empleador', 'verificado', NULL, '$2y$12$wss2siQnx.Hrf5fkWThzJ.rFtSxBjaeoCzSZ2fARl427YE5Mx9FPa', NULL, '2026-11-16 19:25:37', '2026-11-16 19:25:37', NULL, false, '2026-11-16 19:25:36', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (21, 'Yhon Ccama Huanca', 'yhon@tampu.pe', '76789012', '952111222', 'Espinar', 'estudiante', 'no_aplica', NULL, '$2y$12$4RQwI/JxBSlRdLaPbHgOWuoEAUCv5GAuJARFRPkWaWiAsmWnfdcYm', NULL, '2026-11-16 19:25:37', '2026-11-16 19:25:37', NULL, false, '2026-11-16 19:25:37', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (22, 'Milagros Ttito Roque', 'milagros@tampu.pe', '77890123', '952333444', 'Urubamba', 'estudiante', 'no_aplica', NULL, '$2y$12$VLlEknTGt99z0RDuCMw14OGifNn4GRC3JJkuqppZRHYkBMHlIY.RS', NULL, '2026-11-16 19:25:37', '2026-11-16 19:25:37', NULL, false, '2026-11-16 19:25:37', NULL, NULL, NULL, false, NULL, NULL, NULL);
INSERT INTO public.usuarios VALUES (23, 'Brayan Huillca Soncco', 'brayan@tampu.pe', '78901234', '952555666', 'Andahuaylas', 'estudiante', 'no_aplica', NULL, '$2y$12$9G9/sBBvz3oMSBgaVn.Vc.2rKq7ykYfpka14a9G2o2Drr4mgG7HqO', NULL, '2026-11-16 19:25:37', '2026-11-16 19:25:37', NULL, false, '2026-11-16 19:25:37', NULL, NULL, NULL, false, NULL, NULL, NULL);

-- Name: contactos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.contactos_id_seq', 1, false);

-- Name: conversaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.conversaciones_id_seq', 2, true);

-- Name: eventos_seguridad_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.eventos_seguridad_id_seq', 1, false);

-- Name: favoritos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.favoritos_id_seq', 1, false);

-- Name: habitacion_fotos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.habitacion_fotos_id_seq', 54, true);

-- Name: habitaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.habitaciones_id_seq', 25, true);

-- Name: horarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.horarios_id_seq', 7, true);

-- Name: mensajes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.mensajes_id_seq', 5, true);

-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.migrations_id_seq', 8, true);

-- Name: postulaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.postulaciones_id_seq', 9, true);

-- Name: reportes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.reportes_id_seq', 1, true);

-- Name: resenas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.resenas_id_seq', 12, true);

-- Name: solicitudes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.solicitudes_id_seq', 16, true);

-- Name: trabajo_horarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.trabajo_horarios_id_seq', 52, true);

-- Name: trabajos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.trabajos_id_seq', 18, true);

-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

SELECT pg_catalog.setval('public.usuarios_id_seq', 23, true);

-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);

-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);

-- Name: contactos contactos_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.contactos
    ADD CONSTRAINT contactos_pkey PRIMARY KEY (id);

-- Name: conversaciones conversaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.conversaciones
    ADD CONSTRAINT conversaciones_pkey PRIMARY KEY (id);

-- Name: eventos_seguridad eventos_seguridad_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.eventos_seguridad
    ADD CONSTRAINT eventos_seguridad_pkey PRIMARY KEY (id);

-- Name: favoritos favoritos_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_pkey PRIMARY KEY (id);

-- Name: favoritos favoritos_usuario_id_habitacion_id_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_usuario_id_habitacion_id_unique UNIQUE (usuario_id, habitacion_id);

-- Name: habitacion_fotos habitacion_fotos_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.habitacion_fotos
    ADD CONSTRAINT habitacion_fotos_pkey PRIMARY KEY (id);

-- Name: habitaciones habitaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.habitaciones
    ADD CONSTRAINT habitaciones_pkey PRIMARY KEY (id);

-- Name: horarios horarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_pkey PRIMARY KEY (id);

-- Name: horarios horarios_usuario_id_dia_turno_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_usuario_id_dia_turno_unique UNIQUE (usuario_id, dia, turno);

-- Name: mensajes mensajes_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.mensajes
    ADD CONSTRAINT mensajes_pkey PRIMARY KEY (id);

-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);

-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);

-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);

-- Name: postulaciones postulaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.postulaciones
    ADD CONSTRAINT postulaciones_pkey PRIMARY KEY (id);

-- Name: postulaciones postulaciones_trabajo_id_usuario_id_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.postulaciones
    ADD CONSTRAINT postulaciones_trabajo_id_usuario_id_unique UNIQUE (trabajo_id, usuario_id);

-- Name: reportes reportes_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.reportes
    ADD CONSTRAINT reportes_pkey PRIMARY KEY (id);

-- Name: resenas resenas_habitacion_id_usuario_id_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.resenas
    ADD CONSTRAINT resenas_habitacion_id_usuario_id_unique UNIQUE (habitacion_id, usuario_id);

-- Name: resenas resenas_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.resenas
    ADD CONSTRAINT resenas_pkey PRIMARY KEY (id);

-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);

-- Name: solicitudes solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_pkey PRIMARY KEY (id);

-- Name: trabajo_horarios trabajo_horarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.trabajo_horarios
    ADD CONSTRAINT trabajo_horarios_pkey PRIMARY KEY (id);

-- Name: trabajo_horarios trabajo_horarios_trabajo_id_dia_turno_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.trabajo_horarios
    ADD CONSTRAINT trabajo_horarios_trabajo_id_dia_turno_unique UNIQUE (trabajo_id, dia, turno);

-- Name: trabajos trabajos_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.trabajos
    ADD CONSTRAINT trabajos_pkey PRIMARY KEY (id);

-- Name: usuarios usuarios_correo_institucional_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_correo_institucional_unique UNIQUE (correo_institucional);

-- Name: usuarios usuarios_dni_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_dni_unique UNIQUE (dni);

-- Name: usuarios usuarios_email_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_unique UNIQUE (email);

-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);

-- Name: eventos_seguridad_tipo_created_at_index; Type: INDEX; Schema: public; Owner: -

CREATE INDEX eventos_seguridad_tipo_created_at_index ON public.eventos_seguridad USING btree (tipo, created_at);

-- Name: notifications_notifiable_type_notifiable_id_index; Type: INDEX; Schema: public; Owner: -

CREATE INDEX notifications_notifiable_type_notifiable_id_index ON public.notifications USING btree (notifiable_type, notifiable_id);

-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);

-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);

-- Name: conversaciones conversaciones_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.conversaciones
    ADD CONSTRAINT conversaciones_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitaciones(id) ON DELETE SET NULL;

-- Name: conversaciones conversaciones_trabajo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.conversaciones
    ADD CONSTRAINT conversaciones_trabajo_id_foreign FOREIGN KEY (trabajo_id) REFERENCES public.trabajos(id) ON DELETE SET NULL;

-- Name: conversaciones conversaciones_usuario_a_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.conversaciones
    ADD CONSTRAINT conversaciones_usuario_a_id_foreign FOREIGN KEY (usuario_a_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: conversaciones conversaciones_usuario_b_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.conversaciones
    ADD CONSTRAINT conversaciones_usuario_b_id_foreign FOREIGN KEY (usuario_b_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: eventos_seguridad eventos_seguridad_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.eventos_seguridad
    ADD CONSTRAINT eventos_seguridad_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;

-- Name: favoritos favoritos_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitaciones(id) ON DELETE CASCADE;

-- Name: favoritos favoritos_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: habitacion_fotos habitacion_fotos_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.habitacion_fotos
    ADD CONSTRAINT habitacion_fotos_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitaciones(id) ON DELETE CASCADE;

-- Name: habitaciones habitaciones_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.habitaciones
    ADD CONSTRAINT habitaciones_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: horarios horarios_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: mensajes mensajes_conversacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.mensajes
    ADD CONSTRAINT mensajes_conversacion_id_foreign FOREIGN KEY (conversacion_id) REFERENCES public.conversaciones(id) ON DELETE CASCADE;

-- Name: mensajes mensajes_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.mensajes
    ADD CONSTRAINT mensajes_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: postulaciones postulaciones_trabajo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.postulaciones
    ADD CONSTRAINT postulaciones_trabajo_id_foreign FOREIGN KEY (trabajo_id) REFERENCES public.trabajos(id) ON DELETE CASCADE;

-- Name: postulaciones postulaciones_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.postulaciones
    ADD CONSTRAINT postulaciones_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: reportes reportes_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.reportes
    ADD CONSTRAINT reportes_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitaciones(id) ON DELETE CASCADE;

-- Name: reportes reportes_trabajo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.reportes
    ADD CONSTRAINT reportes_trabajo_id_foreign FOREIGN KEY (trabajo_id) REFERENCES public.trabajos(id) ON DELETE CASCADE;

-- Name: reportes reportes_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.reportes
    ADD CONSTRAINT reportes_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: resenas resenas_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.resenas
    ADD CONSTRAINT resenas_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitaciones(id) ON DELETE CASCADE;

-- Name: resenas resenas_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.resenas
    ADD CONSTRAINT resenas_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: solicitudes solicitudes_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitaciones(id) ON DELETE CASCADE;

-- Name: solicitudes solicitudes_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

-- Name: trabajo_horarios trabajo_horarios_trabajo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.trabajo_horarios
    ADD CONSTRAINT trabajo_horarios_trabajo_id_foreign FOREIGN KEY (trabajo_id) REFERENCES public.trabajos(id) ON DELETE CASCADE;

-- Name: trabajos trabajos_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.trabajos
    ADD CONSTRAINT trabajos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

