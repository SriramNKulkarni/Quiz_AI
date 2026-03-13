from langchain_community.vectorstores import FAISS
from langchain_community.embeddings import HuggingFaceEmbeddings

VECTOR_PATH = "vector_store"

# Proper embedding wrapper (not raw SentenceTransformer)
embedding_model = HuggingFaceEmbeddings(
    model_name="sentence-transformers/all-MiniLM-L6-v2"
)

# Load FAISS once (not every request)
vector_store = FAISS.load_local(
    VECTOR_PATH,
    embeddings=embedding_model,
    allow_dangerous_deserialization=True
)


def retrieve_context(grade, subject, chapter):

    grade = str(grade).strip()
    subject = subject.strip() .lower()
    lesson = chapter.strip().lower()

    # Step 1: Get all documents
    all_docs = vector_store.similarity_search("", k=1000)

    # Step 2: Manual metadata filtering
    filtered_docs = [
        doc for doc in all_docs
        if doc.metadata.get("grade") == grade
        and doc.metadata.get("subject") == subject
        and doc.metadata.get("lesson") == lesson
    ]

    if not filtered_docs:
        return None

    # Step 3: Combine filtered content
    context = "\n\n".join([doc.page_content for doc in filtered_docs])
    return context
