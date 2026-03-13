import os
from dotenv import load_dotenv
from google import genai

# Load .env file
load_dotenv()

# Get API key
api_key = os.getenv("GEMINI_API_KEY")

print("API Key Loaded:", api_key is not None)

client = genai.Client(api_key=api_key)

# Now test generation (we will use correct model after seeing list)
response = client.models.generate_content(
    model="models/gemini-2.5-flash",  # temporary guess
    contents="Say hello in one short sentence."
)

print("Response:")
print(response.text)
