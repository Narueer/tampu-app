-- Base de datos TAMPU para PostgreSQL (Supabase)
-- Solo hace falta si se quiere cargar a mano desde el SQL Editor de Supabase.
-- Al desplegar en Render las tablas se crean solas con las migraciones.

-- PostgreSQL database dump


-- Dumped from database version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)

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

-- Name: favoritos id; Type: DEFAULT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos ALTER COLUMN id SET DEFAULT nextval('public.favoritos_id_seq'::regclass);

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

-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -

-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -

-- Data for Name: contactos; Type: TABLE DATA; Schema: public; Owner: -

-- Data for Name: conversaciones; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.conversaciones VALUES (1, 11, 2, 4, NULL, '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.conversaciones VALUES (2, 10, 6, NULL, 1, '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: favoritos; Type: TABLE DATA; Schema: public; Owner: -

-- Data for Name: habitaciones; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.habitaciones VALUES (1, 2, 'Habitación amoblada cerca de la U. Continental', 'Habitación amplia y luminosa en segundo piso, con escritorio, cama de 1.5 plazas y ropero. Ambiente tranquilo para estudiar. Ingreso independiente.', 'Wanchaq', 'Av. Los Incas 512', 'U. Continental', 380.00, 'individual', 'Agua y luz, Wifi, Amoblado', 'img/habitaciones/h1.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5248000, -71.9655000);
INSERT INTO public.habitaciones VALUES (2, 3, 'Cuarto económico en Santiago', 'Cuarto sencillo con cama, mesa y estante. Baño compartido con otro estudiante. Cerca de paraderos.', 'Santiago', 'Jr. Arcopata 230', 'IST Túpac Amaru', 250.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/h2.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5185000, -71.9858000);
INSERT INTO public.habitaciones VALUES (3, 4, 'Habitación con baño propio – San Sebastián', 'Habitación con baño propio y terma. Edificio con portón y vigilancia. A seis minutos caminando de la U. Andina.', 'San Sebastián', 'Calle Las Rosas 145', 'U. Andina', 450.00, 'individual', 'Agua y luz, Wifi, Baño propio, Agua caliente', 'img/habitaciones/h3.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5312000, -71.9381000);
INSERT INTO public.habitaciones VALUES (4, 2, 'Cuarto cerca de la UNSAAC', 'Cuarto en casa familiar a cinco minutos de la UNSAAC. Se puede usar la cocina en horarios acordados.', 'Cusco', 'Av. de la Cultura 1020', 'UNSAAC', 320.00, 'individual', 'Agua y luz, Wifi, Cocina', 'img/habitaciones/h4.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5221000, -71.9588000);
INSERT INTO public.habitaciones VALUES (5, 4, 'Minidepartamento para estudiante', 'Minidepartamento con kitchenette, baño propio y sala de estudio. Ideal para quien busca privacidad.', 'San Sebastián', 'Urb. Tupac Amaru B-12', 'U. Andina', 550.00, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado', 'img/habitaciones/h5.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5336000, -71.9329000);
INSERT INTO public.habitaciones VALUES (6, 3, 'Habitación compartida en Wanchaq', 'Habitación para dos estudiantes, cada uno con su cama y escritorio. Zona segura y con tiendas cerca.', 'Wanchaq', 'Calle Tacna 340', 'U. Continental', 220.00, 'compartida', 'Agua y luz, Wifi, Lavandería', 'img/habitaciones/h6.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5232000, -71.9682000);
INSERT INTO public.habitaciones VALUES (7, 2, 'Habitación amplia en el centro', 'Habitación grande en casona del centro, con ventana a la calle y mucha luz natural.', 'Cusco', 'Calle Saphy 455', 'UNSAAC', 400.00, 'individual', 'Agua y luz, Wifi, Agua caliente', 'img/habitaciones/h7.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5152000, -71.9822000);
INSERT INTO public.habitaciones VALUES (8, 3, 'Cuarto para estudiante – Santiago', 'Cuarto con cama de plaza y media y ropero. Se aceptan estudiantes de institutos.', 'Santiago', 'Av. Antonio Lorena 780', 'IST Túpac Amaru', 270.00, 'individual', 'Agua y luz, Lavandería', 'img/habitaciones/h8.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5226000, -71.9831000);
INSERT INTO public.habitaciones VALUES (9, 4, 'Cuarto con vista – San Jerónimo', 'Cuarto en tercer piso con vista a los cerros. Movilidad directa a la U. Andina.', 'San Jerónimo', 'Av. La Cultura 2450', 'U. Andina', 280.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/h9.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5452000, -71.8893000);
INSERT INTO public.habitaciones VALUES (10, 14, 'Cuarto amplio a solo S/ 150 en el centro', 'Cuarto grande, quedan pocos. Para separarlo deposita 300 soles al número de cuenta que te paso por mensaje.', 'Cusco', 'Calle Nueva Alta 88', 'UNSAAC', 150.00, 'individual', 'Agua y luz, Wifi', 'img/habitaciones/h10.jpg', 'disponible', '2026-11-15 23:07:53', '2026-11-15 23:07:53', -13.5195000, -71.9745000);

-- Data for Name: horarios; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.horarios VALUES (1, 10, 'lunes', 'manana');
INSERT INTO public.horarios VALUES (2, 10, 'martes', 'manana');
INSERT INTO public.horarios VALUES (3, 10, 'miercoles', 'manana');
INSERT INTO public.horarios VALUES (4, 10, 'jueves', 'manana');
INSERT INTO public.horarios VALUES (5, 10, 'viernes', 'manana');
INSERT INTO public.horarios VALUES (6, 10, 'lunes', 'tarde');
INSERT INTO public.horarios VALUES (7, 10, 'miercoles', 'tarde');

-- Data for Name: mensajes; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.mensajes VALUES (1, 1, 11, 'Hola señora Rosa, ¿el cuarto tiene escritorio?', true, '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.mensajes VALUES (2, 1, 2, 'Hola Ana, sí tiene escritorio y una silla. Te espero el día de la visita.', true, '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.mensajes VALUES (3, 1, 11, 'Perfecto, muchas gracias.', false, '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.mensajes VALUES (4, 2, 10, 'Buenas tardes, ¿qué días empezaría?', true, '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.mensajes VALUES (5, 2, 6, 'Hola Luis, puedes empezar este martes a las 2 pm en el local de Santiago.', false, '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.migrations VALUES (1, '2026_11_01_000001_create_usuarios_table', 1);
INSERT INTO public.migrations VALUES (2, '2026_11_01_000002_create_habitaciones_table', 1);
INSERT INTO public.migrations VALUES (3, '2026_11_01_000003_create_trabajos_table', 1);
INSERT INTO public.migrations VALUES (4, '2026_11_15_000001_create_modulos_finales', 1);
INSERT INTO public.migrations VALUES (5, '2026_11_16_000001_create_contactos_y_sesiones', 1);

-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.notifications VALUES ('4b944da9-a802-4708-8d49-ebc5b2dbc0fb', 'App\Notifications\Aviso', 'App\Models\Usuario', 10, '{"texto":"Minimarket Wayra acept\u00f3 tu postulaci\u00f3n a \"Repartidor en bicicleta\"","url":"\/estudiante","icono":"briefcase"}', NULL, '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.notifications VALUES ('45fdc1ee-6a24-47d8-802e-dc58efc606be', 'App\Notifications\Aviso', 'App\Models\Usuario', 3, '{"texto":"Ana Quispe Ccasa quiere visitar \"Habitaci\u00f3n compartida en Wanchaq\"","url":"\/arrendador\/solicitudes","icono":"house-door"}', NULL, '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -

-- Data for Name: postulaciones; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.postulaciones VALUES (1, 1, 10, 'Tengo bicicleta propia y conozco bien Santiago.', 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.postulaciones VALUES (2, 6, 10, 'Tengo inglés básico.', 'pendiente', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.postulaciones VALUES (3, 5, 11, 'Estoy en sexto ciclo de Ingeniería de Sistemas.', 'pendiente', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.postulaciones VALUES (4, 4, 13, NULL, 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.postulaciones VALUES (5, 1, 12, NULL, 'rechazada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: reportes; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.reportes VALUES (1, 13, 10, NULL, 'Pide adelanto sin visita', 'Me pidió depositar 300 soles para separar el cuarto sin dejarme verlo.', 'pendiente', '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: resenas; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.resenas VALUES (1, 1, 10, 5, 'La habitación es igual que en las fotos. La señora Rosa es muy amable y la zona es tranquila.', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.resenas VALUES (2, 1, 12, 4, 'Buena habitación y cerca de la universidad. El wifi a veces falla en las noches.', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.resenas VALUES (3, 3, 13, 5, 'El baño propio y el agua caliente valen la pena. Muy seguro el edificio.', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.resenas VALUES (4, 2, 12, 3, 'Es económico pero el baño compartido es pequeño. Para el precio está bien.', '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -

-- Data for Name: solicitudes; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.solicitudes VALUES (1, 4, 11, '2026-11-17', 'Hola, soy de Abancay y estudio en la UNSAAC. ¿Puedo ir el sábado?', 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.solicitudes VALUES (2, 6, 11, '2026-11-19', '¿La habitación compartida es solo para mujeres?', 'pendiente', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.solicitudes VALUES (3, 1, 10, '2026-11-09', 'Quisiera verla antes de mudarme.', 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.solicitudes VALUES (4, 1, 12, '2026-11-05', 'Hola, estudio en la Continental.', 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.solicitudes VALUES (5, 3, 13, '2026-11-07', '¿Puedo ir por la tarde?', 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.solicitudes VALUES (6, 2, 12, '2026-11-03', 'Me interesa, ¿sigue disponible?', 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.solicitudes VALUES (7, 7, 11, '2026-11-12', 'Hola, ¿puedo verla el lunes?', 'aceptada', '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: trabajo_horarios; Type: TABLE DATA; Schema: public; Owner: -

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

-- Data for Name: trabajos; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.trabajos VALUES (1, 6, 'Repartidor en bicicleta', 'Minimarket Wayra', 'Reparto de pedidos en la zona de Santiago. Se da la bicicleta.', 'Santiago', 'medio tiempo', 40.00, 'dia', 'activa', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.trabajos VALUES (2, 7, 'Soporte técnico sábados', 'Andes Tech SAC', 'Mantenimiento de computadoras e instalación de programas.', 'Wanchaq', 'por horas', 70.00, 'dia', 'activa', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.trabajos VALUES (3, 8, 'Ayudante de cocina (mañanas)', 'Restaurante Inti Raymi', 'Apoyo en la preparación de desayunos y almuerzos.', 'Cusco', 'medio tiempo', 50.00, 'dia', 'activa', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.trabajos VALUES (4, 6, 'Atención en minimarket (noches)', 'Minimarket Wayra', 'Atención al cliente y caja en turno de noche.', 'San Sebastián', 'medio tiempo', 45.00, 'dia', 'activa', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.trabajos VALUES (5, 7, 'Practicante de sistemas', 'Andes Tech SAC', 'Apoyo en desarrollo web y soporte a usuarios. Para estudiantes de Ingeniería de Sistemas.', 'Wanchaq', 'practicas', 1025.00, 'mes', 'activa', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.trabajos VALUES (6, 8, 'Mozo/a fin de semana', 'Restaurante Inti Raymi', 'Atención en mesas los fines de semana. Se valora inglés básico.', 'Cusco', 'medio tiempo', 60.00, 'dia', 'activa', '2026-11-15 23:07:53', '2026-11-15 23:07:53');
INSERT INTO public.trabajos VALUES (7, 9, 'Guía de apoyo turístico', 'Qhapaq Tours', 'Acompañar a grupos de turistas en el centro histórico.', 'Cusco', 'por horas', 80.00, 'dia', 'activa', '2026-11-15 23:07:53', '2026-11-15 23:07:53');

-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: -

INSERT INTO public.usuarios VALUES (1, 'Administrador TAMPU', 'admin@tampu.pe', '70000001', NULL, NULL, 'admin', 'no_aplica', NULL, '$2y$12$oMq6fS.hzmnmpsZlrWyDgOZOvs3QrMphxxUpj0yc.hhtMu82yLa1S', NULL, '2026-11-15 23:07:49', '2026-11-15 23:07:49', NULL, false);
INSERT INTO public.usuarios VALUES (2, 'Rosa Huamán Quispe', 'rosa@tampu.pe', '41234567', '984111222', NULL, 'arrendador', 'verificado', NULL, '$2y$12$MgDzrpRW9bR0ivAmjafFr.MeSsy6Qycu7lHzohNNQ0B7W/53/u3iq', NULL, '2026-11-15 23:07:50', '2026-11-15 23:07:50', NULL, false);
INSERT INTO public.usuarios VALUES (3, 'Julio Ccori Mamani', 'julio@tampu.pe', '42345678', '984333444', NULL, 'arrendador', 'verificado', NULL, '$2y$12$NIWIUy7uJGzlm6X9p1G/N.K243KAFS6i4D8v/7CE8eDmLCYIbdNrG', NULL, '2026-11-15 23:07:50', '2026-11-15 23:07:50', NULL, false);
INSERT INTO public.usuarios VALUES (4, 'Carmen Mamani Huallpa', 'carmen@tampu.pe', '43456789', '984555666', NULL, 'arrendador', 'verificado', NULL, '$2y$12$P8daY0EuyArIZm3W63vQ8OeDAuvHSGdw1QRtXQMV7xgVHFdyaBtly', NULL, '2026-11-15 23:07:50', '2026-11-15 23:07:50', NULL, false);
INSERT INTO public.usuarios VALUES (5, 'Pedro Ccahuana Rojas', 'pedro@tampu.pe', '44567890', '984777888', NULL, 'arrendador', 'pendiente', 'documentos/demo-pedro.png', '$2y$12$Mw3Itx.kaSRXJVxEXayxe.CGNg7SbpHE00e7csHBJq09lczIODqn.', NULL, '2026-11-15 23:07:50', '2026-11-15 23:07:50', NULL, false);
INSERT INTO public.usuarios VALUES (6, 'Lucía Ttito Condori', 'wayra@tampu.pe', '45678901', '974111222', NULL, 'empleador', 'verificado', NULL, '$2y$12$g62PsSd1b0HWDNJG6tIr0e1Pez1sLvBGL4iI7uIw1Spyk8mr/ki4O', NULL, '2026-11-15 23:07:51', '2026-11-15 23:07:51', NULL, false);
INSERT INTO public.usuarios VALUES (7, 'Marco Paredes Soto', 'andestech@tampu.pe', '46789012', '974333444', NULL, 'empleador', 'verificado', NULL, '$2y$12$e6N4SF7KTTGUsZe.ct04G.cVdwWm8ok/EYR6TkpbVWLmxQ4qUQtx2', NULL, '2026-11-15 23:07:51', '2026-11-15 23:07:51', NULL, false);
INSERT INTO public.usuarios VALUES (8, 'Elena Condori Apaza', 'intiraymi@tampu.pe', '47890123', '974555666', NULL, 'empleador', 'verificado', NULL, '$2y$12$bUigkQZoSW1a21YcoB0oL.tBBtyP1V9fiF0wmTumKBcbr1VQcxbiu', NULL, '2026-11-15 23:07:51', '2026-11-15 23:07:51', NULL, false);
INSERT INTO public.usuarios VALUES (9, 'Diego Huallpa Cusi', 'qhapaq@tampu.pe', '48901234', '974777888', NULL, 'empleador', 'pendiente', 'documentos/demo-qhapaq.png', '$2y$12$DugQ/s5wREsZIytFKyqiweoy2T/5j8ire.k7JTgGTDfy00EuKUcKu', NULL, '2026-11-15 23:07:52', '2026-11-15 23:07:52', NULL, false);
INSERT INTO public.usuarios VALUES (10, 'Luis Mamani Choque', 'luis@tampu.pe', '72345678', '951222333', 'Sicuani', 'estudiante', 'no_aplica', NULL, '$2y$12$XALNIG.yMFjLtgXMfsUQ0e3CtStEYanQdH684p/zEG7DkX92Zzom.', NULL, '2026-11-15 23:07:52', '2026-11-15 23:07:52', NULL, false);
INSERT INTO public.usuarios VALUES (11, 'Ana Quispe Ccasa', 'ana@tampu.pe', '73456789', '951444555', 'Abancay', 'estudiante', 'no_aplica', NULL, '$2y$12$PanKWeRibM45XH19r1ivFuT95snHT2fniZ1eMtkNO..F88MM.tv0m', NULL, '2026-11-15 23:07:52', '2026-11-15 23:07:52', NULL, false);
INSERT INTO public.usuarios VALUES (12, 'Rocío Apaza Layme', 'rocio@tampu.pe', '74567890', '951666777', 'Puno', 'estudiante', 'no_aplica', NULL, '$2y$12$MvEKadc.8Lx/Qi.vpAMQ4esDaBnr4INYV17eW52zg9sY0G7/x3IPG', NULL, '2026-11-15 23:07:52', '2026-11-15 23:07:52', NULL, false);
INSERT INTO public.usuarios VALUES (13, 'Kevin Huamán Cusi', 'kevin@tampu.pe', '75678901', '951888999', 'Quillabamba', 'estudiante', 'no_aplica', NULL, '$2y$12$QJyJMpM9GtIaPB2UCBvkludvvjkMzwgKP3Qf8Buv9srsLjsuFtyle', NULL, '2026-11-15 23:07:53', '2026-11-15 23:07:53', NULL, false);
INSERT INTO public.usuarios VALUES (14, 'Mario Sullca Ttito', 'mario@tampu.pe', '49012345', '984999000', NULL, 'arrendador', 'verificado', NULL, '$2y$12$ZN37Sr1ZjeXGH46xAW18TeW81P5o9TXsGhl2XBNxqrbGAxM7ndRYi', NULL, '2026-11-15 23:07:53', '2026-11-15 23:07:53', NULL, false);

-- Name: contactos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: conversaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: favoritos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: habitaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: horarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: mensajes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: postulaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: reportes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: resenas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: solicitudes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: trabajo_horarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: trabajos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -

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

-- Name: favoritos favoritos_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_pkey PRIMARY KEY (id);

-- Name: favoritos favoritos_usuario_id_habitacion_id_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_usuario_id_habitacion_id_unique UNIQUE (usuario_id, habitacion_id);

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

-- Name: usuarios usuarios_dni_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_dni_unique UNIQUE (dni);

-- Name: usuarios usuarios_email_unique; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_unique UNIQUE (email);

-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);

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

-- Name: favoritos favoritos_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitaciones(id) ON DELETE CASCADE;

-- Name: favoritos favoritos_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -

ALTER TABLE ONLY public.favoritos
    ADD CONSTRAINT favoritos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;

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

-- PostgreSQL database dump complete


