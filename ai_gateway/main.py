import os
import json
from datetime import datetime
from dotenv import load_dotenv
from fastapi import FastAPI
from pydantic import BaseModel
from google import genai
from retrieval import retrieve_context
from fastapi.middleware.cors import CORSMiddleware
import mysql.connector

from prompts import academic_quiz_prompt, weekly_topic_prompt, weekly_quiz_prompt


load_dotenv()

api_key = os.getenv("GEMINI_API_KEY")
client = genai.Client(api_key=api_key)

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # change in production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# ===============================
# DATABASE CONNECTION
# ===============================

def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST"),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASS"),
        database=os.getenv("DB_NAME")
    )


# ===============================
# TOKEN ESTIMATION FUNCTION
# ===============================

def estimate_tokens(text: str):
    # Approximation: 1 token ≈ 4 characters
    return int(len(text) / 4)


def estimate_cost(input_tokens, output_tokens):
    # Gemini Flash approximate pricing
    input_cost = (input_tokens / 1_000_000) * 0.35
    output_cost = (output_tokens / 1_000_000) * 0.53
    return round(input_cost + output_cost, 6)


# ===============================
# REQUEST MODELS
# ===============================

class QuizRequest(BaseModel):
    grade: int
    subject: str
    chapter: str
    difficulty: str
    mcq_count: int
    tf_count: int
    fill_count: int
    match_count: int


class WeeklyTopicRequest(BaseModel):
    grade_group: str


class WeeklyQuizRequest(BaseModel):
    grade_group: str
    topic: str
    question_types: list[str]
    num_questions: int
    start_date: datetime
    end_date: datetime


# ==========================================================
# ACADEMIC QUIZ GENERATION
# ==========================================================

@app.post("/generate-quiz")
def generate_quiz(request: QuizRequest):

    context = retrieve_context(
        request.grade,
        request.subject,
        request.chapter
    )

    if not context:
        return {
            "status": "error",
            "error_code": "CONTEXT_NOT_FOUND",
            "message": "No lesson or notes uploaded for this topic."
        }

    prompt = academic_quiz_prompt(context, request)

    # TOKEN ANALYSIS (INPUT)
    input_tokens = estimate_tokens(prompt)

    response = client.models.generate_content(
        model="models/gemini-2.5-flash",
        contents=prompt
    )

    raw_text = response.text.strip()

    if raw_text.startswith("```"):
        raw_text = raw_text.replace("```json", "")
        raw_text = raw_text.replace("```", "")
        raw_text = raw_text.strip()

    # TOKEN ANALYSIS (OUTPUT)
    output_tokens = estimate_tokens(raw_text)
    total_tokens = input_tokens + output_tokens
    cost = estimate_cost(input_tokens, output_tokens)

    print("\n========= TOKEN ANALYSIS =========")
    print(f"Input Tokens  : {input_tokens}")
    print(f"Output Tokens : {output_tokens}")
    print(f"Total Tokens  : {total_tokens}")
    print(f"Estimated Cost: ${cost}")
    print("==================================\n")

    try:
        quiz_json = json.loads(raw_text)
    except json.JSONDecodeError:
        return {
            "status": "error",
            "message": "Failed to parse Gemini response as JSON",
            "raw_output": response.text
        }

    return {
        "status": "success",
        "quiz": quiz_json,
        "token_analysis": {
            "input_tokens": input_tokens,
            "output_tokens": output_tokens,
            "total_tokens": total_tokens,
            "estimated_cost_usd": cost
        }
    }


# ==========================================================
# WEEKLY TOPIC SUGGESTION
# ==========================================================

@app.post("/suggest-weekly-topics")
def suggest_weekly_topics(request: WeeklyTopicRequest):

    prompt = weekly_topic_prompt(request.grade_group)

    response = client.models.generate_content(
        model="models/gemini-2.5-flash",
        contents=prompt
    )

    raw_text = response.text.strip()

    if raw_text.startswith("```"):
        raw_text = raw_text.replace("```json", "")
        raw_text = raw_text.replace("```", "")
        raw_text = raw_text.strip()

    try:
        return json.loads(raw_text)
    except:
        return {"status": "error", "message": "Failed to generate topics"}


# ==========================================================
# WEEKLY QUIZ GENERATION
# ==========================================================

@app.post("/generate-weekly-quiz")
def generate_weekly_quiz(request: WeeklyQuizRequest):

    prompt = weekly_quiz_prompt(request)

    response = client.models.generate_content(
        model="models/gemini-2.5-flash",
        contents=prompt
    )

    raw_text = response.text.strip()

    if raw_text.startswith("```"):
        raw_text = raw_text.replace("```json", "")
        raw_text = raw_text.replace("```", "")
        raw_text = raw_text.strip()

    try:
        quiz_data = json.loads(raw_text)
    except:
        return {"status": "error", "message": "Invalid AI response"}

    conn = get_db_connection()
    cursor = conn.cursor()

    cursor.execute("""
        INSERT INTO weekly_quizzes
        (title, grade_group, topic, total_questions, start_date, end_date, status)
        VALUES (%s, %s, %s, %s, %s, %s, 'active')
    """, (
        f"Weekly Quiz - {request.grade_group}",
        request.grade_group,
        request.topic,
        request.num_questions,
        request.start_date,
        request.end_date
    ))

    weekly_quiz_id = cursor.lastrowid

    for q in quiz_data["questions"]:

        difficulty = q.get("difficulty", "medium").lower().strip()

        if difficulty not in ["easy", "medium", "hard"]:
            difficulty = "medium"

        cursor.execute("""
            INSERT INTO weekly_quiz_questions
            (weekly_quiz_id, question_text, question_type, options_json, correct_answer, marks, difficulty_level)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (
            weekly_quiz_id,
            q.get("question"),
            q.get("type"),
            json.dumps(q.get("options", [])),
            q.get("correct_answer"),
            1,
            difficulty
        ))

    conn.commit()
    cursor.close()
    conn.close()

    return {
        "status": "success",
        "weekly_quiz_id": weekly_quiz_id
    } 