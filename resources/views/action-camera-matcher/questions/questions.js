// let currentQuestionBlockIdx = 0;
const Current_Question_Block_Idx = 0; // 10 ddv, 0 live
let formAnswers = {
    // 0: 0,
    // 1: 2,
    // 2: 1,
    // 3: 2,
    // 4: 0,
    // 5: 0,
    // 6: 0,
    // 7: 0,
    // 8: 2,
    // 9: 0,
    // 10: 0,
};


/**
 * Benefits list
 */
const benefitsList = questionnaire["benefits"];
const affiliateLink = questionnaire["affiliateLink"];

/**
 * Record the answer
 * @param {*} questionIdx
 * @param {*} answer
 * @returns
 */
const recordAnswer = (questionIdx, answer) =>
    (formAnswers[questionIdx] = answer);

const removeAnswer = (questionIdx) => delete answerData[questionIdx];
const getAnswer = (questionIdx) => formAnswers[questionIdx];
const hasAnswer = (questionIdx) => getAnswer(questionIdx) !== undefined;

/**
 * On page load
 * 
 */
const onPageLoad = () => {
    let currentQuestionBlockIdx = Current_Question_Block_Idx;
    const questionBlocks = document.querySelectorAll(
        "[data-question-block-idx]"
    );

    const formQuestions = document.querySelectorAll(
        '[id^="form-question-block-"]'
    );

    const seeResultsButton = document.querySelector("[data-submit]");
    const nextButtons = document.querySelectorAll("[data-next-question]");
    const prevButtons = document.querySelectorAll("[data-previous-question]");
    const inputOptions = document.querySelectorAll("input[type=radio]");

    [...nextButtons, ...prevButtons].forEach((button) =>
        button.addEventListener("click", (event) => {
            event.preventDefault();
            const isNext = button.getAttribute("data-next-question") !== null;
            const newIdx = isNext
                ? currentQuestionBlockIdx + 1
                : currentQuestionBlockIdx - 1;

            if (isNext) {
                if (!hasAnswer(currentQuestionBlockIdx)) {
                    alert("Please select an answer before proceeding");
                    return;
                }

                const questionWeight = Object.entries(questionnaire["weights"]);
                const canProceed = hasResults(questionWeight, formAnswers, newIdx);
                if (!canProceed) {
                    alert("No results available, please select an answer before proceeding");
                    return;
                }
            }

            updateQuestionBlockVisibility(newIdx);
        })
    );

    seeResultsButton.addEventListener("click", async (event) => {
        event.preventDefault();
        await storeAnswers(formAnswers);
        window.location.href = `/action-camera-matcher/results/${btoa(JSON.stringify(formAnswers))}`;
    });

    inputOptions.forEach((radioInput) =>
        radioInput.addEventListener("change", (event) => {
            const value = parseInt(event.target.value);
            recordAnswer(currentQuestionBlockIdx, value);
        })
    );

    /**
     * Update the question block visibility
     * @param {*} blockIdx 
     */
    const updateQuestionBlockVisibility = (blockIdx) => {
        currentQuestionBlockIdx = blockIdx;
        questionBlocks.forEach((questionBlock, idx) =>
            idx === blockIdx
                ? questionBlock.classList.remove("hidden")
                : questionBlock.classList.add("hidden")
        );
    };

    formQuestions[currentQuestionBlockIdx].classList.remove("hidden");
};

window.addEventListener("load", onPageLoad);

/**
 * Has results
 * @param {*} questionWeight 
 * @param {*} formAnswers 
 * @param {*} index 
 * @returns 
 */
const hasResults = (questionWeight, formAnswers, index) => {
    const formAnswersToCurrentIdx = Object.entries(formAnswers).slice(
        0,
        index
    ).reduce((acc, [key, value]) => {
        acc[key] = value;
        return acc;
    }, {});

    return getResults(questionWeight, formAnswersToCurrentIdx).length > 0;
};

/**
 * Get the results based on the weights and answers
 * @param {*} weights
 * @param {*} answers
 * @returns
 */
const getResults = (weights, answers) => {
    const questionWeights = weights.reduce((acc, [name, itemWeights]) => {
        const weightAnswerMap = itemWeights.map((v, i) => v[answers[i]]);
        if (weightAnswerMap.includes("out")) {
            return acc;
        }

        const totalWeight = weightAnswerMap.reduce(
            (sum, weight) => sum + weight,
            0
        );

        return {
            ...acc,
            [name]: totalWeight,
        };
    }, {});

    const weightEntities = Object.entries(questionWeights);
    const values = weightEntities.map(([, value]) => value);
    const maxValue = Math.max(...values);
    const minValue = Math.min(...values);
    const results = weightEntities.map(([key, value]) => {
        const percentage =
            maxValue !== minValue
                ? ((value - minValue) / (maxValue - minValue)) * 100
                : 0;

        return {
            key,
            value,
            percentage,
        };
    });

    results.sort((a, b) => b.percentage - a.percentage);
    return results;
};

/**
 * Store the answers
 * @param {*} answers
 */
const storeAnswers = async (answers) => {
    const site = "https://classermedia.com";
    const endpoint = `${site}/api/site/actions-camera-matcher`;
    return fetch(endpoint, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            answers,
            grc: document.querySelector("#grc-token").value,
        }),
    })
};

/**
 * Render the template with the data
 * @param {*} template
 * @param {*} data
 * @returns
 */
const render = (template, data) => {
    return template.replace(/\${(.*?)}/g, (match, p1) => {
        return data[p1.trim()];
    });
};
