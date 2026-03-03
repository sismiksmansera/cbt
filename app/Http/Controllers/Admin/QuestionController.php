<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Exam;
use App\Models\Subject;
use App\Helpers\OmmlToLatex;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::with(['exam', 'subject', 'options']);
        if ($request->exam_id) $query->where('exam_id', $request->exam_id);
        if ($request->subject_id) $query->where('subject_id', $request->subject_id);
        if ($request->tipe) $query->where('tipe', $request->tipe);

        $questions = $query->latest()->paginate(20);
        $exams = Exam::orderBy('kategori')->get();
        $subjects = Subject::orderBy('nama')->get();
        return view('admin.questions.index', compact('questions', 'exams', 'subjects'));
    }

    public function create(Request $request)
    {
        $exams = Exam::with('subject')->orderBy('kategori')->get();
        $subjects = Subject::orderBy('nama')->get();
        $examId = $request->exam_id;
        return view('admin.questions.create', compact('exams', 'subjects', 'examId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'tipe' => 'required|in:multiple_choice,multiple_answer,true_false,matching,short_answer,essay',
            'pertanyaan' => 'required',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $question = Question::create([
            'exam_id' => $request->exam_id,
            'subject_id' => $exam->subject_id,
            'tipe' => $request->tipe,
            'pertanyaan' => $request->pertanyaan,
            'bobot' => $request->bobot ?? 1,
            'pembahasan' => $request->pembahasan,
            'urutan' => Question::where('exam_id', $request->exam_id)->max('urutan') + 1,
        ]);

        // Handle options based on question type
        if (in_array($request->tipe, ['multiple_choice', 'multiple_answer'])) {
            foreach ($request->options ?? [] as $i => $opt) {
                if (empty($opt['teks'])) continue;
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => $opt['teks'],
                    'is_correct' => isset($opt['correct']),
                    'urutan' => $i,
                ]);
            }
        } elseif ($request->tipe === 'true_false') {
            QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Benar', 'is_correct' => $request->jawaban_benar === 'true', 'urutan' => 0]);
            QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Salah', 'is_correct' => $request->jawaban_benar === 'false', 'urutan' => 1]);
        } elseif ($request->tipe === 'matching') {
            foreach ($request->pairs ?? [] as $i => $pair) {
                if (empty($pair['left'])) continue;
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => $pair['left'],
                    'teks_pasangan' => $pair['right'],
                    'urutan' => $i,
                ]);
            }
        } elseif ($request->tipe === 'short_answer') {
            QuestionOption::create([
                'question_id' => $question->id,
                'teks_opsi' => $request->jawaban_singkat ?? '',
                'is_correct' => true,
                'urutan' => 0,
            ]);
        }

        return redirect()->route('admin.exams.questions', $request->exam_id)->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $question = Question::with('options')->findOrFail($id);
        $exams = Exam::with('subject')->orderBy('kategori')->get();
        return view('admin.questions.edit', compact('question', 'exams'));
    }

    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $request->validate([
            'tipe' => 'required',
            'pertanyaan' => 'required',
        ]);

        $question->update([
            'tipe' => $request->tipe,
            'pertanyaan' => $request->pertanyaan,
            'bobot' => $request->bobot ?? 1,
            'pembahasan' => $request->pembahasan,
        ]);

        // Delete old options and recreate
        $question->options()->delete();

        if (in_array($request->tipe, ['multiple_choice', 'multiple_answer'])) {
            foreach ($request->options ?? [] as $i => $opt) {
                if (empty($opt['teks'])) continue;
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => $opt['teks'],
                    'is_correct' => isset($opt['correct']),
                    'urutan' => $i,
                ]);
            }
        } elseif ($request->tipe === 'true_false') {
            QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Benar', 'is_correct' => $request->jawaban_benar === 'true', 'urutan' => 0]);
            QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Salah', 'is_correct' => $request->jawaban_benar === 'false', 'urutan' => 1]);
        } elseif ($request->tipe === 'matching') {
            foreach ($request->pairs ?? [] as $i => $pair) {
                if (empty($pair['left'])) continue;
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => $pair['left'],
                    'teks_pasangan' => $pair['right'],
                    'urutan' => $i,
                ]);
            }
        } elseif ($request->tipe === 'short_answer') {
            QuestionOption::create([
                'question_id' => $question->id,
                'teks_opsi' => $request->jawaban_singkat ?? '',
                'is_correct' => true,
                'urutan' => 0,
            ]);
        }

        return redirect()->route('admin.exams.questions', $question->exam_id)->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $question = Question::with('options')->findOrFail($id);
        $examId = $question->exam_id;
        $this->deleteQuestionImages($question);
        $question->delete();
        return redirect()->route('admin.exams.questions', $examId)->with('success', 'Soal berhasil dihapus.');
    }

    private function deleteQuestionImages($question)
    {
        $htmlTexts = [$question->pertanyaan];
        foreach ($question->options as $opt) {
            $htmlTexts[] = $opt->teks_opsi;
        }
        foreach ($htmlTexts as $html) {
            if (preg_match_all('/src="(\/images\/questions\/[^"]+)"/', $html ?? '', $matches)) {
                foreach ($matches[1] as $path) {
                    $fullPath = public_path($path);
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'format' => 'required|in:blackboard,gift,word_table,word_text',
            'file' => 'required|file',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $imported = 0;

        if ($request->format === 'word_table') {
            $imported = $this->parseWordTable($request->file('file')->getRealPath(), $exam);
        } elseif ($request->format === 'word_text') {
            $imported = $this->parseWordText($request->file('file')->getRealPath(), $exam);
        } else {
            $content = file_get_contents($request->file('file')->getRealPath());
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');

            if ($request->format === 'blackboard') {
                $imported = $this->parseBlackboard($content, $exam);
            } else {
                $imported = $this->parseGift($content, $exam);
            }
        }

        return redirect()->route('admin.exams.questions', $exam->id)
                         ->with('success', "Berhasil mengimport {$imported} soal.");
    }

    public function importAnswerKey(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $updated = 0;
        $headerFound = false;

        foreach ($rows as $row) {
            $colA = trim($row['A'] ?? '');
            $colB = strtoupper(trim($row['B'] ?? ''));
            $colC = $row['C'] ?? null;

            // Skip header row
            if (!$headerFound) {
                if (preg_match('/no|nomor|num/i', $colA) || preg_match('/kunci|jawaban|key|answer/i', $colB)) {
                    $headerFound = true;
                    continue;
                }
                // If first row has a number, treat as data (no header)
                if (is_numeric($colA)) {
                    $headerFound = true;
                } else {
                    continue;
                }
            }

            if (!is_numeric($colA) || !preg_match('/^[A-E]$/', $colB)) continue;

            $questionNo = (int)$colA;
            $answerKey = $colB;
            $bobot = is_numeric($colC) ? (float)$colC : null;

            // Find question by urutan
            $question = Question::where('exam_id', $exam->id)
                                ->where('urutan', $questionNo)
                                ->first();
            if (!$question) continue;

            // Update weight if provided
            if ($bobot !== null) {
                $question->update(['bobot' => $bobot]);
            }

            // Set correct answer: reset all options, then mark the correct one
            $answerIndex = ord($answerKey) - ord('A') + 1;
            QuestionOption::where('question_id', $question->id)->update(['is_correct' => false]);
            QuestionOption::where('question_id', $question->id)
                          ->where('urutan', $answerIndex)
                          ->update(['is_correct' => true]);

            $updated++;
        }

        return redirect()->route('admin.exams.questions', $exam->id)
                         ->with('success', "Berhasil mengupdate kunci jawaban untuk {$updated} soal.");
    }

    private $imageCounter = 0;

    private function extractCellContent($element)
    {
        $content = '';

        if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
            $text = $element->getText() ?? '';
            $fontStyle = $element->getFontStyle();
            if ($fontStyle && is_object($fontStyle)) {
                if (method_exists($fontStyle, 'isSuperScript') && $fontStyle->isSuperScript()) {
                    return '<sup>' . $text . '</sup>';
                }
                if (method_exists($fontStyle, 'isSubScript') && $fontStyle->isSubScript()) {
                    return '<sub>' . $text . '</sub>';
                }
            }
            return $text;
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
            return "\n";
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\Image) {
            $imgPath = $this->saveWordImage($element);
            if ($imgPath) {
                return '<img src="' . $imgPath . '" style="max-width:100%;">';
            }
            return '';
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $content .= $this->extractCellContent($child);
            }
        } elseif (method_exists($element, 'getText')) {
            $content .= $element->getText() ?? '';
        }

        return $content;
    }

    private function saveWordImage($imageElement)
    {
        try {
            $this->imageCounter++;
            $dir = public_path('images/questions');
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $imageSource = $imageElement->getSource();
            if ($imageSource && file_exists($imageSource)) {
                $ext = pathinfo($imageSource, PATHINFO_EXTENSION) ?: 'png';
                $filename = 'import_' . time() . '_' . $this->imageCounter . '.' . $ext;
                copy($imageSource, $dir . '/' . $filename);
                return '/images/questions/' . $filename;
            }

            if (method_exists($imageElement, 'getImageStringData')) {
                $data = $imageElement->getImageStringData(true);
                if ($data) {
                    $filename = 'import_' . time() . '_' . $this->imageCounter . '.png';
                    file_put_contents($dir . '/' . $filename, base64_decode($data));
                    return '/images/questions/' . $filename;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getCellContent($cell)
    {
        $parts = [];
        foreach ($cell->getElements() as $el) {
            if ($el instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                $parts[] = '';
                continue;
            }
            $text = $this->extractCellContent($el);
            if ($text !== '') {
                $parts[] = $text;
            }
        }
        return trim(implode("\n", $parts));
    }

    private function cellHasListItem($cell)
    {
        foreach ($cell->getElements() as $el) {
            if ($el instanceof \PhpOffice\PhpWord\Element\ListItemRun ||
                $el instanceof \PhpOffice\PhpWord\Element\ListItem) {
                return true;
            }
        }
        return false;
    }

    private function parseWordTable($filePath, $exam)
    {
        $processedPath = OmmlToLatex::preprocessDocx($filePath);
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($processedPath);
        $imported = 0;
        $urutan = Question::where('exam_id', $exam->id)->max('urutan') ?? 0;
        $this->imageCounter = 0;

        $currentQuestion = null;
        $currentOptions = [];

        $saveQuestion = function () use ($exam, &$urutan, &$imported, &$currentQuestion, &$currentOptions) {
            if (!$currentQuestion || empty(trim(strip_tags($currentQuestion)))) return;

            $urutan++;
            $question = Question::create([
                'exam_id' => $exam->id,
                'subject_id' => $exam->subject_id,
                'pertanyaan' => trim($currentQuestion),
                'tipe' => 'multiple_choice',
                'bobot' => 1,
                'urutan' => $urutan,
            ]);

            foreach ($currentOptions as $idx => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => trim($opt['text']),
                    'is_correct' => $opt['correct'],
                    'urutan' => $idx + 1,
                ]);
            }

            $imported++;
        };

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (!($element instanceof \PhpOffice\PhpWord\Element\Table)) continue;

                foreach ($element->getRows() as $row) {
                    $cells = $row->getCells();
                    if (count($cells) < 2) continue;

                    $cellContents = [];
                    foreach ($cells as $cell) {
                        $cellContents[] = $this->getCellContent($cell);
                    }

                    $col1 = trim($cellContents[0] ?? '');
                    $col2 = trim($cellContents[1] ?? '');
                    $col3 = isset($cellContents[2]) ? trim($cellContents[2]) : '';
                    $numCols = count($cellContents);

                    $isAutoNumbered = $this->cellHasListItem($cells[0]);

                    // Question row: col1 has a number OR first cell uses auto-numbering
                    if (preg_match('/^\s*\d+[\.\)]*\s*$/', $col1) || $isAutoNumbered) {
                        $saveQuestion();
                        // For 3+ columns, merge col2 and col3 as question text
                        if ($numCols >= 3 && $col3 !== '') {
                            $currentQuestion = $col2 . "\n" . $col3;
                        } else {
                            $currentQuestion = $col2;
                        }
                        $currentOptions = [];
                    }
                    // Option row in 2-col: col1 is letter A-E (optional = prefix)
                    elseif (preg_match('/^\s*(=?)\s*([A-Ea-e])[\.\)]*\s*$/', $col1, $m)) {
                        $isCorrect = ($m[1] === '=');
                        // For 3+ columns with letter in col1, merge col2+col3 as answer
                        if ($numCols >= 3 && $col3 !== '') {
                            $answerText = $col2 . "\n" . $col3;
                        } else {
                            $answerText = $col2;
                        }
                        $currentOptions[] = [
                            'text' => $answerText,
                            'correct' => $isCorrect,
                        ];
                    }
                    // 3-col option/continuation: col1 empty, col2 might be letter
                    elseif ($col1 === '' && $currentQuestion !== null) {
                        if ($numCols >= 3 && preg_match('/^\s*(=?)\s*([A-Ea-e])[\.\)]*\s*$/', $col2, $m2)) {
                            // col2 = letter, col3 = answer text
                            $isCorrect = ($m2[1] === '=');
                            $currentOptions[] = [
                                'text' => $col3,
                                'correct' => $isCorrect,
                            ];
                        } elseif ($col2 !== '') {
                            // Continuation of question text
                            if ($numCols >= 3 && $col3 !== '') {
                                $currentQuestion .= "\n" . $col2 . "\n" . $col3;
                            } else {
                                $currentQuestion .= "\n" . $col2;
                            }
                        }
                    }
                }
            }
        }

        $saveQuestion();
        if ($processedPath !== $filePath) @unlink($processedPath);
        return $imported;
    }

    private function parseWordText($filePath, $exam)
    {
        $processedPath = OmmlToLatex::preprocessDocx($filePath);
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($processedPath);
        $imported = 0;
        $urutan = Question::where('exam_id', $exam->id)->max('urutan') ?? 0;
        $this->imageCounter = 0;

        $currentQuestion = null;
        $currentOptions = [];
        $currentOptionText = null;
        $currentOptionCorrect = false;
        $state = 'IDLE'; // IDLE, IN_QUESTION, IN_OPTIONS
        $breakCount = 0;

        $flushOption = function () use (&$currentOptions, &$currentOptionText, &$currentOptionCorrect) {
            if ($currentOptionText !== null && trim(strip_tags($currentOptionText)) !== '') {
                $currentOptions[] = [
                    'text' => trim($currentOptionText),
                    'correct' => $currentOptionCorrect,
                ];
            }
            $currentOptionText = null;
            $currentOptionCorrect = false;
        };

        $saveQuestion = function () use ($exam, &$urutan, &$imported, &$currentQuestion, &$currentOptions, &$state, $flushOption) {
            $flushOption();
            if (!$currentQuestion || empty(trim(strip_tags($currentQuestion)))) return;

            $urutan++;
            $question = Question::create([
                'exam_id' => $exam->id,
                'subject_id' => $exam->subject_id,
                'pertanyaan' => trim($currentQuestion),
                'tipe' => 'multiple_choice',
                'bobot' => 1,
                'urutan' => $urutan,
            ]);

            foreach ($currentOptions as $idx => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => trim($opt['text']),
                    'is_correct' => $opt['correct'],
                    'urutan' => $idx + 1,
                ]);
            }

            $imported++;
            $currentQuestion = null;
            $currentOptions = [];
        };

        $questionNumId = null; // Track the numId used by question items
        $numFormats = $this->getNumberingFormats($processedPath); // numId-depth -> format details
        $numCounters = []; // Track counters per numId-depth for prefix generation

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                // Skip tables
                if ($element instanceof \PhpOffice\PhpWord\Element\Table) continue;

                // Track consecutive TextBreaks
                if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                    $breakCount++;
                    // Only save on double-break when options have been collected
                    if ($breakCount >= 2 && $state === 'IN_OPTIONS') {
                        $saveQuestion();
                        $state = 'IDLE';
                    }
                    continue;
                }
                $breakCount = 0;

                // Handle ListItemRun / ListItem
                $isListItem = ($element instanceof \PhpOffice\PhpWord\Element\ListItemRun ||
                               $element instanceof \PhpOffice\PhpWord\Element\ListItem);

                if ($isListItem) {
                    $depth = method_exists($element, 'getDepth') ? (int)$element->getDepth() : 0;
                    $paraText = trim($this->extractCellContent($element));
                    if ($paraText === '') continue;

                    // Get numId
                    $numId = null;
                    $listStyle = method_exists($element, 'getStyle') ? $element->getStyle() : null;
                    if ($listStyle && is_object($listStyle) && method_exists($listStyle, 'getNumId')) {
                        $numId = $listStyle->getNumId();
                    }

                    // Check numbering format from numbering.xml
                    $formatKey = "$numId-$depth";
                    $numFormat = $numFormats[$formatKey] ?? null;
                    $fmtType = is_array($numFormat) ? ($numFormat['fmt'] ?? 'bullet') : 'bullet';
                    $isLetterFormat = ($fmtType === 'upperLetter');
                    $isDecimalFormat = ($fmtType === 'decimal');

                    // Track counters per numId-depth for prefix generation
                    if (!isset($numCounters[$formatKey])) {
                        $numCounters[$formatKey] = is_array($numFormat) ? ($numFormat['start'] ?? 1) : 1;
                    }
                    $currentCounter = $numCounters[$formatKey];

                    // 3-way classification:
                    // 1. Letter format (A,B,C,D,E) → answer option
                    // 2. Decimal at depth=0 with question numId → new question
                    // 3. Everything else (bullets, numbered sub-items) → question text with prefix
                    if ($isLetterFormat) {
                        // This is an answer option (A, B, C, D, E)
                        if ($state === 'IN_QUESTION' || $state === 'IN_OPTIONS') {
                            $flushOption();
                            $currentOptionText = $paraText;
                            $currentOptionCorrect = false;
                            $state = 'IN_OPTIONS';
                        }
                        $numCounters[$formatKey]++;
                    } elseif ($isDecimalFormat && $depth === 0) {
                        // Decimal numbered item at top level = question
                        if ($questionNumId === null) $questionNumId = $numId;
                        if ($numId === $questionNumId) {
                            if ($state !== 'IDLE') $saveQuestion();
                            $currentQuestion = $paraText;
                            $currentOptions = [];
                            $currentOptionText = null;
                            $state = 'IN_QUESTION';
                        } else {
                            // Different decimal numId (numbered sub-items) → question text with prefix
                            $prefix = $this->generateNumberPrefix($numFormat, $currentCounter);
                            if ($state === 'IN_QUESTION' && $currentQuestion !== null) {
                                $currentQuestion .= "\n" . $prefix . $paraText;
                            } elseif ($state === 'IN_OPTIONS' && $currentOptionText !== null) {
                                $currentOptionText .= "\n" . $prefix . $paraText;
                            }
                        }
                        $numCounters[$formatKey]++;
                    } else {
                        // Bullets or other format → append with prefix
                        $prefix = $this->generateNumberPrefix($numFormat, $currentCounter);
                        if ($state === 'IN_QUESTION' && $currentQuestion !== null) {
                            $currentQuestion .= "\n" . $prefix . $paraText;
                        } elseif ($state === 'IN_OPTIONS' && $currentOptionText !== null) {
                            $currentOptionText .= "\n" . $prefix . $paraText;
                        } elseif ($state === 'IDLE') {
                            $currentQuestion = $prefix . $paraText;
                            $currentOptions = [];
                            $currentOptionText = null;
                            $state = 'IN_QUESTION';
                        }
                        $numCounters[$formatKey]++;
                    }
                    continue;
                }

                // Handle regular TextRun paragraphs
                $paraText = trim($this->extractCellContent($element));
                if ($paraText === '') continue;

                // Check if text starts with "N." pattern (manually typed question number)
                if (preg_match('/^(\d+)\.\s+(.+)$/s', $paraText, $qm)) {
                    if ($state !== 'IDLE') $saveQuestion();
                    $currentQuestion = trim($qm[2]);
                    $currentOptions = [];
                    $currentOptionText = null;
                    $state = 'IN_QUESTION';
                }
                // Check if text starts with "A." to "E." (manually typed option)
                elseif (preg_match('/^([A-E])\. (.+)$/s', $paraText, $om)) {
                    $flushOption();
                    $currentOptionCorrect = false;
                    $currentOptionText = trim($om[2]);
                    $state = 'IN_OPTIONS';
                }
                // Continuation of current context
                else {
                    if ($state === 'IN_OPTIONS' && $currentOptionText !== null) {
                        $currentOptionText .= "\n" . $paraText;
                    } elseif ($state === 'IN_QUESTION' && $currentQuestion !== null) {
                        $currentQuestion .= "\n" . $paraText;
                    }
                }
            }
        }

        $saveQuestion();
        if ($processedPath !== $filePath) @unlink($processedPath);
        return $imported;
    }

    /**
     * Read numbering.xml from a .docx and return detailed format info
     * Returns: "numId-depth" => ['fmt' => 'decimal', 'lvlText' => '%1)', 'start' => 1]
     */
    private function getNumberingFormats($docxPath)
    {
        $formats = [];
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) return $formats;

        $xml = $zip->getFromName('word/numbering.xml');
        $zip->close();
        if (!$xml) return $formats;

        $doc = new \DOMDocument();
        @$doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        // Build abstractNum map: abstractNumId -> [ilvl -> info]
        $abstractNums = [];
        foreach ($xpath->query('//w:abstractNum') as $an) {
            $anId = $an->getAttribute('w:abstractNumId');
            $levels = [];
            foreach ($xpath->query('.//w:lvl', $an) as $lvl) {
                $ilvl = $lvl->getAttribute('w:ilvl');
                $fmtNode = $xpath->query('.//w:numFmt', $lvl)->item(0);
                $txtNode = $xpath->query('.//w:lvlText', $lvl)->item(0);
                $startNode = $xpath->query('.//w:start', $lvl)->item(0);
                $levels[$ilvl] = [
                    'fmt' => $fmtNode ? $fmtNode->getAttribute('w:val') : 'bullet',
                    'lvlText' => $txtNode ? $txtNode->getAttribute('w:val') : '',
                    'start' => $startNode ? (int)$startNode->getAttribute('w:val') : 1,
                ];
            }
            $abstractNums[$anId] = $levels;
        }

        // Map numId -> abstractNumId -> details
        foreach ($xpath->query('//w:num') as $num) {
            $numId = $num->getAttribute('w:numId');
            $anIdNode = $xpath->query('.//w:abstractNumId', $num)->item(0);
            if (!$anIdNode) continue;

            $anId = $anIdNode->getAttribute('w:val');
            $baseLevels = $abstractNums[$anId] ?? [];

            // Check for level overrides
            foreach ($xpath->query('.//w:lvlOverride', $num) as $override) {
                $ilvl = $override->getAttribute('w:ilvl');
                $fmtNode = $xpath->query('.//w:numFmt', $override)->item(0);
                if ($fmtNode && isset($baseLevels[$ilvl])) {
                    $baseLevels[$ilvl]['fmt'] = $fmtNode->getAttribute('w:val');
                }
                $startNode = $xpath->query('.//w:startOverride', $override)->item(0);
                if ($startNode && isset($baseLevels[$ilvl])) {
                    $baseLevels[$ilvl]['start'] = (int)$startNode->getAttribute('w:val');
                }
            }

            foreach ($baseLevels as $ilvl => $info) {
                $formats["$numId-$ilvl"] = $info;
            }
        }

        return $formats;
    }

    /**
     * Generate the numbering prefix for a list item (e.g., "1)", "(a)", "•")
     */
    private function generateNumberPrefix($numFormat, $counter)
    {
        $fmt = $numFormat['fmt'] ?? 'bullet';
        $lvlText = $numFormat['lvlText'] ?? '';

        if ($fmt === 'bullet') {
            return '• ';
        }

        // Generate the number/letter value
        $value = '';
        if ($fmt === 'decimal') {
            $value = (string)$counter;
        } elseif ($fmt === 'lowerLetter') {
            $value = chr(ord('a') + $counter - 1);
        } elseif ($fmt === 'upperLetter') {
            $value = chr(ord('A') + $counter - 1);
        } elseif ($fmt === 'lowerRoman') {
            $romans = [1=>'i',2=>'ii',3=>'iii',4=>'iv',5=>'v',6=>'vi',7=>'vii',8=>'viii',9=>'ix',10=>'x'];
            $value = $romans[$counter] ?? (string)$counter;
        } elseif ($fmt === 'upperRoman') {
            $romans = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
            $value = $romans[$counter] ?? (string)$counter;
        } else {
            $value = (string)$counter;
        }

        // Apply lvlText pattern (e.g., "%1)" → "1)", "(%1)" → "(1)")
        if ($lvlText) {
            $prefix = str_replace('%1', $value, $lvlText);
            // Also handle %2, %3 etc. by replacing with value
            $prefix = preg_replace('/%\d/', $value, $prefix);
            return $prefix . ' ';
        }

        return $value . ') ';
    }

    private function parseBlackboard($content, $exam)
    {
        $lines = preg_split('/\r?\n/', $content);
        $imported = 0;
        $urutan = Question::where('exam_id', $exam->id)->max('urutan') ?? 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = explode("\t", $line);
            if (count($parts) < 3) continue;

            $type = strtoupper(trim($parts[0]));
            $questionText = trim($parts[1]);

            if ($type === 'MC' && count($parts) >= 4) {
                $urutan++;
                $question = Question::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $exam->subject_id,
                    'tipe' => 'multiple_choice',
                    'pertanyaan' => $questionText,
                    'bobot' => 1,
                    'urutan' => $urutan,
                ]);

                // Options: pairs of (answer_text, correct/incorrect)
                for ($i = 2; $i < count($parts) - 1; $i += 2) {
                    $optText = trim($parts[$i]);
                    $isCorrect = strtolower(trim($parts[$i + 1] ?? '')) === 'correct';
                    if (empty($optText)) continue;

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'teks_opsi' => $optText,
                        'is_correct' => $isCorrect,
                        'urutan' => ($i - 2) / 2,
                    ]);
                }
                $imported++;

            } elseif ($type === 'TF') {
                $urutan++;
                $answer = strtolower(trim($parts[2]));
                $question = Question::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $exam->subject_id,
                    'tipe' => 'true_false',
                    'pertanyaan' => $questionText,
                    'bobot' => 1,
                    'urutan' => $urutan,
                ]);

                QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Benar', 'is_correct' => $answer === 'true', 'urutan' => 0]);
                QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Salah', 'is_correct' => $answer === 'false', 'urutan' => 1]);
                $imported++;

            } elseif ($type === 'ESS') {
                $urutan++;
                Question::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $exam->subject_id,
                    'tipe' => 'essay',
                    'pertanyaan' => $questionText,
                    'bobot' => 1,
                    'urutan' => $urutan,
                ]);
                $imported++;

            } elseif ($type === 'SA') {
                $urutan++;
                $question = Question::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $exam->subject_id,
                    'tipe' => 'short_answer',
                    'pertanyaan' => $questionText,
                    'bobot' => 1,
                    'urutan' => $urutan,
                ]);

                $answerText = trim($parts[2] ?? '');
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => $answerText,
                    'is_correct' => true,
                    'urutan' => 0,
                ]);
                $imported++;
            }
        }

        return $imported;
    }

    private function parseGift($content, $exam)
    {
        $imported = 0;
        $urutan = Question::where('exam_id', $exam->id)->max('urutan') ?? 0;

        // Normalize line endings
        $content = str_replace("\r\n", "\n", $content);

        // Split into question blocks (separated by blank lines)
        $blocks = preg_split('/\n{2,}/', $content);

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block) || substr($block, 0, 2) === '//') continue;

            // Remove title ::Title::
            $block = preg_replace('/^::.*?::/', '', $block);
            $block = trim($block);
            if (empty($block)) continue;

            // Find the answer block by matching braces from the end
            // This correctly handles LaTeX {} in question text like \frac{a}{b}
            $lastBrace = strrpos($block, '}');
            if ($lastBrace === false) continue;

            $depth = 0;
            $answerStart = false;
            for ($i = $lastBrace; $i >= 0; $i--) {
                if ($block[$i] === '}') $depth++;
                elseif ($block[$i] === '{') $depth--;
                if ($depth === 0) {
                    $answerStart = $i;
                    break;
                }
            }
            if ($answerStart === false) continue;

            $questionText = trim(substr($block, 0, $answerStart));
            $answerBlock = trim(substr($block, $answerStart + 1, $lastBrace - $answerStart - 1));

            if (empty($questionText)) continue;

            // True/False: {T}, {F}, {TRUE}, {FALSE}
            if (preg_match('/^(T|F|TRUE|FALSE)$/i', $answerBlock, $tfMatch)) {
                $urutan++;
                $isTrue = in_array(strtoupper($tfMatch[1]), ['T', 'TRUE']);
                $question = Question::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $exam->subject_id,
                    'tipe' => 'true_false',
                    'pertanyaan' => $questionText,
                    'bobot' => 1,
                    'urutan' => $urutan,
                ]);
                QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Benar', 'is_correct' => $isTrue, 'urutan' => 0]);
                QuestionOption::create(['question_id' => $question->id, 'teks_opsi' => 'Salah', 'is_correct' => !$isTrue, 'urutan' => 1]);
                $imported++;
                continue;
            }

            // Short answer: {=answer} or {=answer1 =answer2}
            if (preg_match('/^=([^~]+)$/', $answerBlock, $saMatch) && strpos($answerBlock, '~') === false) {
                $urutan++;
                $question = Question::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $exam->subject_id,
                    'tipe' => 'short_answer',
                    'pertanyaan' => $questionText,
                    'bobot' => 1,
                    'urutan' => $urutan,
                ]);
                QuestionOption::create([
                    'question_id' => $question->id,
                    'teks_opsi' => trim($saMatch[1]),
                    'is_correct' => true,
                    'urutan' => 0,
                ]);
                $imported++;
                continue;
            }

            // Multiple choice / Multiple answer with percentage scoring
            if (strpos($answerBlock, '=') !== false || strpos($answerBlock, '~') !== false) {
                $urutan++;

                // Check if it uses percentage scoring like ~%50% or ~%-33.3333%
                $hasPercentage = preg_match('/%[-\d.]+%/', $answerBlock);

                $question = Question::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $exam->subject_id,
                    'tipe' => $hasPercentage ? 'multiple_answer' : 'multiple_choice',
                    'pertanyaan' => $questionText,
                    'bobot' => 1,
                    'urutan' => $urutan,
                ]);

                // Parse options: split by ~ or = (keeping the delimiter)
                $optParts = preg_split('/(?=[=~])/', $answerBlock);
                $optOrder = 0;
                foreach ($optParts as $part) {
                    $part = trim($part);
                    if (empty($part)) continue;

                    $isCorrect = false;
                    $optSkor = 0;
                    $prefix = substr($part, 0, 1); // = or ~

                    $optContent = trim(substr($part, 1));

                    // Check for percentage pattern: %50% or %-33.3333%
                    if (preg_match('/^%(-?[\d.]+)%\s*(.*)$/s', $optContent, $pctMatch)) {
                        $optSkor = floatval($pctMatch[1]);
                        $optContent = trim($pctMatch[2]);
                        $isCorrect = $optSkor > 0;
                    } else {
                        // Standard format: = means correct, ~ means wrong
                        $isCorrect = $prefix === '=';
                        $optSkor = $isCorrect ? 100 : 0;
                    }

                    // Remove feedback (# comment)
                    if (strpos($optContent, '#') !== false) {
                        $optContent = trim(substr($optContent, 0, strpos($optContent, '#')));
                    }

                    if (empty($optContent)) continue;

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'teks_opsi' => $optContent,
                        'is_correct' => $isCorrect,
                        'skor' => $optSkor,
                        'urutan' => $optOrder++,
                    ]);
                }
                $imported++;
            }
        }

        return $imported;
    }
}
