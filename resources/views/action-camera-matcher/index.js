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

const onPageLoad = () => {
    let currentQuestionBlockIdx = 0;
    const questionBlocks = document.querySelectorAll(
        "[data-question-block-idx]"
    );

    const formQuestions = document.querySelectorAll(
        '[id^="form-question-block-"]'
    );

    const formResults = document.querySelector("[data-results]");
    const classerBillboard = document.querySelector("[data-classer-billboard]");
    const seeResultsButton = document.querySelector("[data-submit]");
    const resetButton = document.querySelector("[data-reset]");
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
            }

            updateQuestionBlockVisibility(newIdx);
        })
    );

    seeResultsButton.addEventListener("click", (event) => {
        event.preventDefault();
        const questionWeight = Object.entries(questionnaire["weights"]);
        const results = getResults(questionWeight, formAnswers);

        showResults(results);
        storeAnswers(formAnswers);
    });

    inputOptions.forEach((radioInput) =>
        radioInput.addEventListener("change", (event) => {
            const value = parseInt(event.target.value);
            recordAnswer(currentQuestionBlockIdx, value);
        })
    );

    resetButton.addEventListener("click", (event) => {
        event.preventDefault();
        formAnswers = {};
        currentQuestionBlockIdx = 0;

        questionBlocks.forEach((questionBlock) =>
            questionBlock.classList.add("hidden")
        );
        formQuestions[currentQuestionBlockIdx].classList.remove("hidden");
        formResults.classList.add("hidden");
        classerBillboard.classList.add("hidden");
        document.querySelectorAll("input[type=radio]").forEach((radioInput) => {
            radioInput.checked = false;
        });
    });

    const updateQuestionBlockVisibility = (blockIdx) => {
        currentQuestionBlockIdx = blockIdx;
        questionBlocks.forEach((questionBlock, idx) =>
            idx === blockIdx
                ? questionBlock.classList.remove("hidden")
                : questionBlock.classList.add("hidden")
        );
    };

    const showResults = (results) => {
        const loadingItems = Array.from({
            length: results.length,
        });

        formResults.querySelector("ul").innerHTML = loadingItems
            .map((_, i) => renderDummyResult(i))
            .join("");

        setTimeout(() => {
            formResults.querySelector("ul").innerHTML = results
                .map(renderResult)
                .join("");

            const benefitsList = formResults.querySelectorAll(".benefits-list");
            formResults
                .querySelectorAll("[data-toggle-open]")
                .forEach((toggleOpenButton) => {
                    toggleOpenButton.addEventListener("click", (event) => {
                        const index =
                            event.target.getAttribute("data-toggle-open");
                        benefitsList.forEach((benefits, i) =>
                            i === parseInt(index)
                                ? benefits.classList.toggle("hidden")
                                : benefits.classList.add("hidden")
                        );
                    });
                });
        }, Math.floor(Math.random() * 1000) + 1400);

        classerBillboard.classList.remove("hidden");
        formResults.classList.remove("hidden");
        classerBillboard.classList.remove("hidden");
        questionBlocks.forEach((questionBlock) =>
            questionBlock.classList.add("hidden")
        );
    };

    formQuestions[currentQuestionBlockIdx].classList.remove("hidden");
};

window.addEventListener("load", onPageLoad);

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
            recommendationKey: getRecommendationKey(percentage),
            recommendation: getRecommendation(percentage),
            color: getRankedColors(percentage),
        };
    });

    return results;
};

/**
 * Store the answers
 * @param {*} answers
 */
const storeAnswers = (answers) => {
    const site = "https://classermedia.com";
    const endpoint = `${site}/api/site/actions-camera-matcher`;
    fetch(endpoint, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            answers,
            grc: document.querySelector("#grc-token").value,
        }),
    }).then(() => {});
};

/**
 * Get the ranked colors based on the percentage
 * @param {*} percentage
 * @returns
 */
const getRankedColors = (percentage) => {
    if (percentage > 80) {
        return "green";
    } else if (percentage > 60) {
        return "orange";
    } else if (percentage > 40) {
        return "yellow";
    }

    return "red";
};

/**
 * Get the recommendation key based on the percentage
 * @param {*} percentage
 * @returns
 */
const getRecommendationKey = (percentage) => {
    if (percentage > 80) {
        return "highly-recommended";
    } else if (percentage > 50) {
        return "good-match";
    }

    return "might-like";
    // return "not-recommended";
};

/**
 * Get the recommendation based on the percentage
 * @param {*} percentage
 * @returns
 */
const getRecommendation = (percentage) => {
    if (percentage > 80) {
        return "Highly recommend!";
    } else if (percentage > 60) {
        return "It's a good match!";
    }

    return "You might like it!";
    // return "Not recommended!";
};

/**
 * Get the questionnaire
 * @returns
 */
const renderToggleOpenButton = (index) =>
    render(
        document.getElementById("template-acm-results-toggle-benefits-button")
            .innerHTML,
        { index }
    );

/**
 * Get the questionnaire
 * @returns
 */
const renderTitle = (data) =>
    render(
        document.getElementById("template-acm-results-title").innerHTML,
        data
    );

/**
 * Get the questionnaire
 * @returns
 */
const renderBenefits = (key) => {
    const benefits = benefitsList[key] || [];
    const template = document.getElementById(
        "template-acm-results-benefits-item"
    ).innerHTML;
    return `<ul class="benefits-list hidden grid grid-cols-2">
        ${benefits.map((benefit) => render(template, { benefit })).join("")}
    </ul>`;
};

/**
 * Get the questionnaire
 * @returns
 */
const renderResult = (item, i, renderToggle = true) => {
    const template = document.getElementById(
        "template-acm-results-item"
    ).innerHTML;
    const data = {
        title: renderTitle(item),
        benefits: renderBenefits(item.key),
        recommendationKey: item.recommendationKey,
        toggleBenefitsStateButton: renderToggle
            ? renderToggleOpenButton(i)
            : "",
    };

    return render(template, data);
};

/**
 * Get the questionnaire
 * @returns
 */
const renderDummyResult = (i) => {
    const dummy = {
        key: "Fetching results...",
        recommendation: "Just a moment",
        recommendationKey: "might-like",
        color: "green",
    };

    return renderResult(dummy, i, false);
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
