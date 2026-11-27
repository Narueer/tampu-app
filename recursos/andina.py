# música original para TAMPU: charango (cuerdas pulsadas) + quena (flauta) + bombo suave, escala pentatónica andina
import numpy as np, wave
SR = 44100
BPM = 84
beat = 60 / BPM
compases = 16                      # 16 compases de 4 tiempos ≈ 45 s, se repite sin corte
largo = int(SR * beat * 4 * compases)
mix = np.zeros(largo)
rng = np.random.default_rng(7)

def nota(n):  # n = semitonos desde La 220
    return 220 * 2 ** (n / 12)

def charango(f, dur, vol=0.25):
    # Karplus-Strong: ruido que se va suavizando = cuerda pulsada
    N = int(SR / f); buf = rng.uniform(-1, 1, N); out = np.zeros(int(SR * dur))
    for i in range(len(out)):
        out[i] = buf[i % N]
        buf[i % N] = 0.996 * 0.5 * (buf[i % N] + buf[(i + 1) % N])
    return out * vol

def quena(f, dur, vol=0.16):
    t = np.arange(int(SR * dur)) / SR
    vib = 1 + 0.006 * np.sin(2 * np.pi * 5.2 * t) * np.clip(t / 0.25, 0, 1)
    fase = 2 * np.pi * f * np.cumsum(vib) / SR
    tono = np.sin(fase) + 0.18 * np.sin(2 * fase) + 0.05 * np.sin(3 * fase)
    soplo = rng.normal(0, 0.05, len(t))
    soplo = np.convolve(soplo, np.ones(30) / 30, 'same')
    env = np.clip(t / 0.08, 0, 1) * np.clip((dur - t) / 0.15, 0, 1)
    return (tono + soplo) * env * vol

def bombo(vol=0.32):
    t = np.arange(int(SR * 0.35)) / SR
    return np.sin(2 * np.pi * (70 * np.exp(-t * 9) + 45) * t) * np.exp(-t * 11) * vol

def poner(x, inicio):
    i = int(inicio * SR); j = min(largo, i + len(x)); mix[i:j] += x[:j - i]

# acordes en La menor pentatónica: Am - C - G - Am / Am - Em - G - Am
acordes = [[0, 3, 7], [3, 7, 10], [-2, 2, 5], [0, 3, 7], [0, 3, 7], [-5, -2, 2], [-2, 2, 5], [0, 3, 7]]
# rasgueo de huayno: ta - ta-ta | ta - ta-ta
patron = [0, 0.5, 0.75, 1.5, 2, 2.5, 2.75, 3.5]
for c in range(compases):
    ac = acordes[c % len(acordes)]
    t0 = c * 4 * beat
    for k, p in enumerate(patron):
        for s, n in enumerate(ac):
            poner(charango(nota(n + 12), 0.9, 0.11 if k % 2 else 0.15), t0 + p * beat + s * 0.012)
    for p in (0, 2):
        poner(bombo(), t0 + p * beat)

# melodía de quena (entra en el compás 4, descansa al final para que el bucle respire)
pent = [0, 3, 5, 7, 10, 12, 15, 17]
melodia = [(4, 2), (5, 1), (4, 1), (3, 2), (2, 2), (3, 1), (4, 1), (2, 1), (1, 1), (0, 4),
           (2, 2), (3, 1), (4, 1), (5, 2), (4, 2), (3, 1), (2, 1), (1, 1), (2, 1), (0, 4),
           (4, 1), (5, 1), (6, 2), (5, 1), (4, 1), (3, 2), (4, 2), (2, 2), (1, 2), (0, 4)]
t = 4 * 4 * beat
for (g, d) in melodia:
    if t + d * beat > largo / SR - 4 * beat: break
    poner(quena(nota(pent[g] + 12), d * beat * 0.95), t); t += d * beat

# eco suave (como en una quebrada) y cierre sin clic para que el bucle sea continuo
eco = np.zeros_like(mix); r = int(SR * 0.27)
eco[r:] = mix[:-r] * 0.28
mix = mix + eco
fade = int(SR * 0.04); mix[:fade] *= np.linspace(0, 1, fade); mix[-fade:] *= np.linspace(1, 0, fade)
mix = mix / np.max(np.abs(mix)) * 0.8
with wave.open('andina.wav', 'wb') as w:
    w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes((mix * 32767).astype(np.int16).tobytes())
print(round(largo / SR, 1), 's')
