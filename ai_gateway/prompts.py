# prompts.py

def academic_quiz_prompt(context, request):

    return f"""
You are an educational assessment generator.

STRICT RULES:
- Use ONLY the curriculum content provided below.
- Do NOT invent information.
- Do NOT use outside knowledge.
- Follow exact JSON schema.
- Do NOT include markdown formatting.

CURRICULUM CONTENT:
---------------------
{context}
---------------------

Generate:
- {request.mcq_count} Multiple Choice Questions (mcq)
- {request.tf_count} True/False Questions (true_false)
- {request.fill_count} Fill in the blanks (fill_blank)

Match Question Rules:
- Generate ONLY ONE match question
- The match question must contain exactly {request.match_count} pairs
- Each pair must contain "left" and "right"
- Do NOT generate multiple match questions

Difficulty Level: {request.difficulty}
Grade: {request.grade}
Subject: {request.subject}
Chapter: {request.chapter}

Return ONLY valid JSON in this format:

{{
  "questions": [

    {{
      "type": "mcq",
      "question": "Question text",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "correct_answer": "Correct option"
    }},

    {{
      "type": "true_false",
      "question": "Statement",
      "correct_answer": "True"
    }},

    {{
      "type": "fill_blank",
      "question": "Sentence with ____ blank",
      "correct_answer": "Answer"
    }},

    {{
      "type": "match",
      "question": "Match the following",
      "pairs": [
        {{"left": "Item A", "right": "Item 1"}},
        {{"left": "Item B", "right": "Item 2"}}
      ]
    }}

  ]
}}
"""


def weekly_topic_prompt(grade_group):

    return f"""
You are a weekly enrichment topic selector for an educational platform.

Grade Group: {grade_group}

Your task is to suggest 5 engaging, knowledge-expanding, age-appropriate topics.

Rules:
1. Topics must be intellectually stimulating.
2. They may extend beyond the formal curriculum.
3. They must build critical thinking or real-world awareness.
4. They must NOT involve politics, religion, or controversial themes.
5. They must be culturally respectful and safe.
6. They must suit the cognitive level of the specified grade group.
7. Topics should spark curiosity and expand knowledge base.
8. Avoid repetitive or purely textbook chapter names.

Return strictly valid JSON:

{{
  "topics": ["Topic 1", "Topic 2", "Topic 3", "Topic 4", "Topic 5"]
}}
"""


def weekly_quiz_prompt(request):

    return f"""
You are an enrichment weekly quiz generator.

Generate {request.num_questions} questions on:

Topic: {request.topic}
Grade Group: {request.grade_group}

Rules:
- Questions must increase in difficulty
- Use only these question types: {request.question_types}
- Return strictly valid JSON
- Do NOT include markdown

Format:

{{
  "questions": [
    {{
      "type": "MCQ",
      "question": "",
      "options": [],
      "correct_answer": "",
      "difficulty": "easy"
    }}
  ]
}}
"""