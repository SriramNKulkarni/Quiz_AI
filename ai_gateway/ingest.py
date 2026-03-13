import os
import shutil
from dotenv import load_dotenv
from pypdf import PdfReader
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_community.vectorstores import FAISS
from sentence_transformers import SentenceTransformer

# -----------------------------
# CONFIG
# -----------------------------
DATA_PATH = "../Data"
VECTOR_PATH = "vector_store"

print("Loading local embedding model...")
model = SentenceTransformer("all-MiniLM-L6-v2")

documents = []  
metadatas = []

print("\n🚀 Starting curriculum ingestion...\n")


# DELETE OLD VECTOR STORE

if os.path.exists(VECTOR_PATH):
    print("Removing old vector store...")
    shutil.rmtree(VECTOR_PATH)


# READ GRADE → SUBJECT → PDF

for grade in os.listdir(DATA_PATH):

    grade_path = os.path.join(DATA_PATH, grade)
    if not os.path.isdir(grade_path):
        continue

    grade_clean = str(grade).strip()

    print(f"\nProcessing Grade: {grade_clean}")

    for subject in os.listdir(grade_path):

        subject_path = os.path.join(grade_path, subject)
        if not os.path.isdir(subject_path):
            continue

        subject_clean = subject.strip().lower()

        print(f"Subject: {subject_clean}")

        for file in os.listdir(subject_path):

            if not file.lower().endswith(".pdf"):
                continue

            file_path = os.path.join(subject_path, file)
            lesson_name = os.path.splitext(file)[0].strip().lower()

            print(f"      📄 Reading: {file}")

            reader = PdfReader(file_path)
            text = ""

            for page in reader.pages:
                extracted = page.extract_text()
                if extracted:
                    text += extracted + "\n"

            if text.strip():
                documents.append(text)
                metadatas.append({
                    "grade": grade_clean,
                    "subject": subject_clean,
                    "lesson": lesson_name
                })


# SPLIT INTO CHUNKS

print("\n✂ Splitting documents into chunks...\n")

splitter = RecursiveCharacterTextSplitter(
    chunk_size=500,
    chunk_overlap=100
)

docs = []
meta = []

for doc, metadata in zip(documents, metadatas):
    chunks = splitter.split_text(doc)
    for chunk in chunks:
        if chunk.strip():
            docs.append(chunk)
            meta.append(metadata)

print(f"📊 Total lessons: {len(documents)}")
print(f"📦 Total chunks: {len(docs)}")

# -----------------------------
# GENERATE EMBEDDINGS
# -----------------------------
print("\n🧠 Generating embeddings locally...\n")

embeddings = model.encode(
    docs,
    show_progress_bar=True,
    convert_to_numpy=True
)

# -----------------------------
# CREATE FAISS STORE
# -----------------------------
print("\n💾 Creating FAISS vector store...\n")

vector_store = FAISS.from_embeddings(
    list(zip(docs, embeddings)),
    embedding=None,
    metadatas=meta
)

vector_store.save_local(VECTOR_PATH)

print("\n✅ Ingestion Complete Successfully.")

print("\nSample metadata entries:")
for i in range(min(5, len(meta))):
    print(meta[i])
