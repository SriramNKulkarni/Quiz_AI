# LEAP AI Quiz Generation System

An AI-powered quiz generation system built for the **LEAP EdTech Platform**.  
The system automatically generates quizzes from verified curriculum content using a **Retrieval-Augmented Generation (RAG)** pipeline to ensure accuracy and prevent hallucinated questions.

The platform integrates **PHP, FastAPI, MySQL, FAISS, and Google Gemini** to generate structured quizzes aligned with academic content.

---

# Overview

The LEAP AI Quiz System allows teachers or administrators to generate quizzes dynamically based on curriculum documents.  

Instead of relying on a general AI model alone, the system first retrieves **relevant curriculum content** from a vector database and then instructs the AI to generate quiz questions strictly from that content.

This ensures:

- Curriculum alignment
- Reduced hallucination
- Consistent academic structure
- Automatic quiz generation

---

# Key Features

### AI-Based Quiz Generation
Automatically generates quiz questions from curriculum content using **Google Gemini**.

### Retrieval-Augmented Generation (RAG)
Uses a **FAISS vector database** to retrieve the most relevant educational content before generating questions.

### Multiple Question Types
Supports multiple academic question formats:

- Multiple Choice Questions (MCQ)
- True / False
- Fill in the Blank
- Match the Following

### Intelligent Scoring System
The system evaluates answers and supports **partial scoring for match-type questions**.

### Curriculum-Grounded AI
The AI model is **restricted to retrieved curriculum context**, preventing fabricated questions.

### Database Driven
All generated quizzes, questions, and answers are stored in **MySQL** for easy retrieval and evaluation.

---

# System Architecture
User Interface (PHP)
|
v
Quiz Request API (PHP Backend)
|
v
FastAPI AI Gateway
|
v
Context Retrieval (FAISS Vector Store)
|
v
Google Gemini AI Model
|
v
Structured Quiz JSON Response
|
v
MySQL Database Storage
|
v
Quiz Rendering in PHP


---

# Tech Stack

## Frontend
- PHP
- HTML
- JavaScript
- CSS

## Backend
- PHP
- FastAPI (Python)

## AI & Machine Learning
- Google Gemini API
- Retrieval-Augmented Generation (RAG)
- FAISS Vector Database

## Database
- MySQL

## Data Processing
- Python
- LangChain-style document retrieval pipeline

---

# Project Structure


LEAP-AI-Quiz-System
│
├── api/
│ ├── main.py
│ ├── retrieval.py
│ ├── prompts.py
│ └── ingest.py
│
├── php/
│ ├── generate.php
│ ├── submit_quiz.php
│ └── config.php
│
├── vectorstore/
│ └── FAISS index files
│
├── curriculum/
│ └── curriculum PDFs and documents
│
├── database/
│ └── MySQL schema
│
└── README.md


---

# Quiz Generation Workflow

1. The user requests quiz generation from the PHP interface.
2. PHP sends the request to the **FastAPI AI Gateway**.
3. The system retrieves relevant curriculum content using **FAISS vector search**.
4. The retrieved context is injected into the **AI prompt**.
5. Google Gemini generates quiz questions using only the provided context.
6. The response is validated and stored in **MySQL**.
7. The quiz is rendered on the web interface.

---

# Example Quiz Request

The system can generate quizzes by specifying:

- Grade
- Subject
- Topic
- Number of questions
- Question types

Example:


Grade: 8
Subject: Science
Topic: Photosynthesis

Requested Questions:

5 MCQ

2 True/False

2 Fill in the Blanks

1 Match the Following


---

# Installation Guide

## 1. Clone the Repository


git clone https://github.com/yourusername/leap-ai-quiz-system.git


---

## 2. Setup Python Environment


cd api
python -m venv venv
venv\Scripts\activate

pip install -r requirements.txt


---

## 3. Configure Environment Variables

Create a `.env` file inside the API directory:


GEMINI_API_KEY=your_api_key_here


---

## 4. Run the FastAPI Server


uvicorn main:app --reload


---

## 5. Setup MySQL Database

Import the provided schema file.

Example tables include:

- quizzes
- questions
- answers
- quiz_attempts

---

## 6. Configure PHP Backend

Update the database connection inside:


config.php


---

# RAG Data Ingestion

Before generating quizzes, curriculum documents must be embedded into the vector store.

Run:


python ingest.py


This will:

1. Read curriculum documents
2. Convert them into embeddings
3. Store them in the FAISS vector database

---

# Security Measures

To maintain academic reliability:

- The AI model is restricted to **retrieved curriculum context**
- Strict JSON output validation is applied
- AI is prevented from using external knowledge

---

# Future Improvements

The following features are currently under development:

- Automated Question Paper Generation
- Teacher Dashboard
- Quiz Difficulty Calibration
- Performance Analytics
- Student Progress Tracking

---

# Author

**Sriram N Kulkarni**  
Computer Science Engineering Student  

Skills:
- Machine Learning
- Data Science
- Backend Development
- AI System Integration

---

# License

This project is developed for educational and research purposes.

