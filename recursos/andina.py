# Música original de TAMPU (hecha con código, sin samples): andino tranquilo para acompañar sin cansar.
# Guitarra arpegiada + zampoña suave + bajo + bombo y shaker discretos, con reverberación.
# Todo está afinado exacto (síntesis aditiva), en La menor pentatónica, progresión Am - F - C - G.
#   python andina.py andina.wav   ->   ffmpeg -i andina.wav -b:a 96k andina.mp3
import numpy as np, wave, sys

SR = 44100
BPM = 80
NEGRA = 60 / BPM
COMPASES = 16                                  # 16 compases de 4/4 = 48 s, se repite sin corte
LARGO = int(SR * NEGRA * 4 * COMPASES)
rng = np.random.default_rng(11)

def hz(n):            # n = semitonos desde La 220 Hz
    return 220.0 * 2 ** (n / 12)

def poner(pista, señal, t):
    i = int(t * SR) % LARGO
    fin = i + len(señal)
    if fin <= LARGO:
        pista[i:fin] += señal
    else:                                      # lo que sobra entra por el inicio: el loop no tiene corte
        k = LARGO - i
        pista[i:] += señal[:k]
        pista[:fin - LARGO] += señal[k:]

def guitarra(f, dur=2.2, vol=0.2):
    # cuerda de nylon pulsada: armónicos que se apagan antes cuanto más agudos
    t = np.arange(int(SR * dur)) / SR
    s = np.zeros_like(t)
    for k in range(1, 9):
        if f * k > 9000: break
        s += (0.9 / k ** 1.3) * np.sin(2 * np.pi * f * k * t + rng.uniform(0, 6.28)) * np.exp(-t * (1.6 + 0.9 * k))
    ataque = np.clip(t / 0.004, 0, 1)
    return s * ataque * vol

def zampona(f, dur, vol=0.12):
    # flauta de pan: tono casi puro, algo de soplo y un vibrato que aparece de a poco
    t = np.arange(int(SR * dur)) / SR
    vib = 1 + 0.0045 * np.sin(2 * np.pi * 5.0 * t) * np.clip((t - 0.25) / 0.4, 0, 1)
    fase = 2 * np.pi * f * np.cumsum(vib) / SR
    tono = np.sin(fase) + 0.12 * np.sin(2 * fase) + 0.03 * np.sin(3 * fase)
    soplo = np.convolve(rng.normal(0, 1, len(t)), np.ones(12) / 12, 'same') * 0.06
    env = np.clip(t / 0.09, 0, 1) * np.clip((dur - t) / 0.22, 0, 1)
    return (tono + soplo) * env * vol

def bajo(f, dur, vol=0.22):
    t = np.arange(int(SR * dur)) / SR
    s = np.sin(2 * np.pi * f * t) + 0.25 * np.sin(4 * np.pi * f * t)
    return s * np.clip(t / 0.01, 0, 1) * np.exp(-t * 1.4) * vol

def bombo(vol=0.32):
    t = np.arange(int(SR * 0.45)) / SR
    f = 95 * np.exp(-t * 9) + 45
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 7) * vol

def shaker(vol=0.035):
    t = np.arange(int(SR * 0.09)) / SR
    r = rng.normal(0, 1, len(t)); r = r - np.convolve(r, np.ones(6) / 6, 'same')   # solo agudos
    return r * np.clip(t / 0.01, 0, 1) * np.exp(-t * 45) * vol

def colchon(notas, dur, vol=0.045):
    # acorde sostenido muy suave de fondo
    t = np.arange(int(SR * dur)) / SR
    s = sum(np.sin(2 * np.pi * hz(n) * t) + 0.3 * np.sin(2 * np.pi * hz(n) * 2.003 * t) for n in notas)
    env = np.clip(t / 0.8, 0, 1) * np.clip((dur - t) / 0.8, 0, 1)
    return s * env * vol

# acordes (semitonos desde La 220): Am, F, C, G
ACORDES = [
    ('Am', [-12, 0, 3, 7, 12], -24),
    ('F', [-16, -4, 0, 3, 8], -28),
    ('C', [-9, 3, 7, 10, 15], -21),
    ('G', [-14, -2, 2, 5, 10], -26),
]
guit = np.zeros(LARGO); flauta = np.zeros(LARGO); grave = np.zeros(LARGO); perc = np.zeros(LARGO); fondo = np.zeros(LARGO)
PATRON = [0, 2, 3, 4, 3, 2, 1, 3]                # arpegio en corcheas
for c in range(COMPASES):
    nombre, notas, raiz = ACORDES[c % 4]
    t0 = c * 4 * NEGRA
    for i, idx in enumerate(PATRON):
        poner(guit, guitarra(hz(notas[idx]), vol=0.16 if i % 2 else 0.2), t0 + i * NEGRA / 2 + rng.uniform(0, 0.008))
    poner(grave, bajo(hz(raiz), NEGRA * 1.9), t0)
    poner(grave, bajo(hz(raiz + 7), NEGRA * 1.9, 0.16), t0 + 2 * NEGRA)
    poner(fondo, colchon(notas[1:4], 4 * NEGRA + 0.8), t0)
    if c >= 2:                                    # la percusión entra en el compás 3
        poner(perc, bombo(), t0); poner(perc, bombo(0.22), t0 + 2.5 * NEGRA)
        for k in range(8):
            poner(perc, shaker(0.022 if k % 2 == 0 else 0.012), t0 + k * NEGRA / 2)

# melodía de zampoña (La menor pentatónica), entra en el compás 5 y se repite con una variación
A, C, D, E, G = 0, 3, 5, 7, 10
FRASE1 = [(E + 12, 1.5), (D + 12, 0.5), (C + 12, 1), (A + 12, 1), (C + 12, 1.5), (D + 12, 0.5), (E + 12, 2),
          (G + 12, 1), (E + 12, 1), (D + 12, 1.5), (C + 12, 0.5), (D + 12, 4)]
FRASE2 = [(E + 12, 1.5), (G + 12, 0.5), (A + 24, 1), (G + 12, 1), (E + 12, 1.5), (D + 12, 0.5), (C + 12, 2),
          (D + 12, 1), (C + 12, 1), (A + 12, 1.5), (G, 0.5), (A + 12, 4)]
t = 4 * 4 * NEGRA
for frase in (FRASE1, FRASE2, FRASE1, FRASE2):
    for n, d in frase:
        poner(flauta, zampona(hz(n), d * NEGRA * 0.98), t)
        t += d * NEGRA
    if t >= LARGO / SR - 0.1: break

def reverb(x, segundos=1.8, mezcla=0.22):
    # respuesta de una sala: ruido que se apaga; se aplica en forma circular (sin corte en el loop)
    n = int(SR * segundos); tt = np.arange(n) / SR
    ir = rng.normal(0, 1, n) * np.exp(-tt * 6.9 / segundos)
    ir = np.convolve(ir, np.ones(20) / 20, 'same'); ir /= np.sqrt((ir ** 2).sum())
    L = len(x); F = 1 << int(np.ceil(np.log2(L + n)))
    y = np.fft.irfft(np.fft.rfft(x, F) * np.fft.rfft(ir, F), F)
    mojado = y[:L].copy(); mojado[:len(y) - L] += y[L:len(y)][:L] if len(y) > L else 0
    mojado = mojado[:L]
    return x * (1 - mezcla) + mojado * mezcla * 2.2

mezcla = guit * 1.0 + flauta * 1.0 + grave * 0.9 + perc * 0.8 + fondo
mezcla = reverb(mezcla)
# estéreo simple: guitarra un poco a la izquierda, flauta un poco a la derecha
izq = mezcla + 0.18 * guit - 0.12 * flauta
der = mezcla - 0.18 * guit + 0.12 * flauta
est = np.stack([izq, der], 1)
est = est / np.abs(est).max() * 0.8                                       # pico a -2 dB, sin saturar
salida = sys.argv[-1] if sys.argv[-1].endswith('.wav') else 'andina.wav'
with wave.open(salida, 'w') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR)
    w.writeframes((est * 32767).astype(np.int16).tobytes())
print(salida, round(LARGO / SR, 1), 's')
