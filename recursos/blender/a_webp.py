# recorta el render, quita el velo transparente del fondo y lo guarda como .webp liviano
import sys
from PIL import Image
import numpy as np
def a_webp(src, dst, ancho, pad=8):
    im = Image.open(src).convert('RGBA'); a = np.array(im).astype(np.float32)
    a[..., 3] = np.clip((a[..., 3] - 14) / (255 - 14) * 255, 0, 255)
    im = Image.fromarray(a.astype(np.uint8), 'RGBA')
    bb = im.getchannel('A').point(lambda v: 255 if v > 8 else 0).getbbox(); im = im.crop(bb)
    w, h = im.size; c = Image.new('RGBA', (w + 2 * pad, h + 2 * pad), (0, 0, 0, 0)); c.alpha_composite(im, (pad, pad)); im = c
    im.thumbnail((ancho, ancho * 2), Image.LANCZOS); im.save(dst, 'WEBP', quality=86, alpha_quality=100, exact=True, method=6)
    print(dst, im.size)
if __name__ == '__main__':
    a_webp(sys.argv[1], sys.argv[2], int(sys.argv[3]))
