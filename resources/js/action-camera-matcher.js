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
};

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

    const questionWeight = Object.entries(questionnaire["weights"]);
    const results = getResults(questionWeight, formAnswers);
    console.log(results);

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
        const totalWeight = weightAnswerMap.reduce(
            (sum, weight) => weight == 'out' ? sum : sum + weight,
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

    return weightEntities.map(([key, value]) => {
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
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            console.log("Success:", data);
        })
        .catch((error) => {
            console.error("Error:", error);
        });
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
    } else if (percentage > 60) {
        return "good-match";
    } else if (percentage > 40) {
        return "might-like";
    }

    return "not-recommended";
};

/**
 * Get the recommendation based on the percentage
 * @param {*} percentage
 * @returns
 */
const getRecommendation = (percentage) => {
    if (percentage > 80) {
        return "Highly recommended!";
    } else if (percentage > 60) {
        return "It's a good match!";
    } else if (percentage > 40) {
        return "You can might like it!";
    }

    return "Not recommended!";
};

/**
 * Get the questionnaire
 * @returns
 */
const renderToggleOpenButton = (index) => {
    return `
    <button data-toggle-open="${index}" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-500 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>`;
};

/**
 * Get the questionnaire
 * @returns
 */
const renderTitle = ({ key, recommendation }) => {
    return `<div class="flex-1 min-w-0">
        <p class="text-md font-bold text-gray-700 truncate dark:text-white">
            ${key}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            ${recommendation}
        </p>
    </div>`;
};

/**
 * Get the questionnaire
 * @returns
 */
const renderBenefits = () => {
    const benefits = [
        "Individual configuration",
        "No setup, or hidden fees",
        "Team size: 1 developer",
        "Premium support: 6 months",
    ];

    return `<ul class="benefits-list hidden grid grid-cols-2">
        ${benefits
            .map(
                (benefit) => `
            <li class="flex items-center space-x-3 rtl:space-x-reverse">
                <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                </svg>
                <span>${benefit}</span>
            </li>
        `
            )
            .join("")}
    </ul>`;
};

/**
 * Get the questionnaire
 * @returns
 */
const renderResult = (
    item,
    i,
    renderToggle = true
) => `<li class="recommendation-item ${item.recommendationKey}">
    <div class="flex items-center space-x-4">
    <div class="indicator"></div>
        ${renderTitle(item)}
        ${renderToggle ? renderToggleOpenButton(i) : ""}
    </div>
    ${renderBenefits()}
</li>`;

/**
 * Get the questionnaire
 * @returns
 */
const renderDummyResult = (i) =>
    renderResult(
        {
            key: "Loading...",
            recommendation: "Just a moment",
            recommendationKey: "highly-recommended",
            color: "green",
        },
        i,
        false
    );