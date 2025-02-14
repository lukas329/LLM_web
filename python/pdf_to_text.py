import sys
import cv2
import numpy as np
import fitz  # PyMuPDF
from pdf2image import convert_from_path
from PyPDF2 import PdfReader
import easyocr
import re
import os

# ✅ Inicializácia EasyOCR so slovenčinou
reader = easyocr.Reader(['sk'])

def is_text_pdf(pdf_path):
    """Skontroluje, či PDF obsahuje čitateľný text."""
    with fitz.open(pdf_path) as doc:
        for page in doc:
            text = page.get_text("text")
            if text.strip():
                return True
    return False

def preprocess_image_cv(image):
    """Vylepšenie obrázka pre OCR pomocou OpenCV."""
    img = np.array(image)
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    thresh = cv2.adaptiveThreshold(
        gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, 11, 2
    )
    return thresh

def clean_text(text):
    """Vyčistí nežiaduce znaky a nadbytočné medzery."""
    text = re.sub(r'\s+', ' ', text)  # Odstráni nadbytočné medzery a nové riadky
    text = re.sub(r'[^a-zA-ZáčďéíľňóôřšťúýžÁČĎÉÍĽŇÓÔŘŠŤÚÝŽ0-9.,:;()/-]', ' ', text)  # Povolené znaky
    text = re.sub(r'\s([.,:;])', r'\1', text)  # Odstráni medzeru pred interpunkciou
    return text.strip()

def extract_text_with_easyocr(pdf_path):
    """Použije EasyOCR na extrakciu textu zo skenovaného PDF."""
    images = convert_from_path(pdf_path, dpi=200)
    text = ""

    for image in images:
        preprocessed_img = preprocess_image_cv(image)
        result = reader.readtext(preprocessed_img, detail=0)
        text += "\n".join(result) + "\n"

    return clean_text(text)

def extract_text_from_pdf(pdf_path):
    """Najprv skúsi extrahovať text normálne, ak zlyhá, použije OCR."""
    text = ""

    if is_text_pdf(pdf_path):
        reader = PdfReader(pdf_path)
        for page in reader.pages:
            text += page.extract_text() or ""
    else:
        print(f"⚠️ PDF {pdf_path} je skenované – spúšťam OCR...")
        text = extract_text_with_easyocr(pdf_path)

    return clean_text(text)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Použitie: python pdf_to_text.py <input_pdf_path> <output_txt_path>")
        sys.exit(1)

    input_pdf_path = sys.argv[1]
    output_txt_path = sys.argv[2]

    if not os.path.exists(input_pdf_path):
        print(f"❌ Chyba: Súbor {input_pdf_path} neexistuje.")
        sys.exit(1)

    text = extract_text_from_pdf(input_pdf_path)

    with open(output_txt_path, "w", encoding="utf-8") as file:
        file.write(text)

    print(f"✅ Text extrahovaný do: {output_txt_path}")
